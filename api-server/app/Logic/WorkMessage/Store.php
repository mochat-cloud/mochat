<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkMessage;

use App\Constants\WorkMessage\MsgType;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use App\Contract\WorkMessageIndexServiceInterface;
use App\Contract\WorkMessageServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use MoChat\WeWorkFinanceSDK\WxFinanceSDK;
use Hyperf\Redis\Redis;

class Store
{
    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpClient;

    /**
     * @Inject
     * @var WorkMessageConfigServiceInterface
     */
    protected $msgConfigClient;

    /**
     * @var WorkMessageServiceInterface
     */
    protected $msgClient;

    /**
     * @Inject
     * @var WorkMessageIndexServiceInterface
     */
    protected $msgIndexClient;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $roomClient;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $employeeClient;

    /**
     * @Inject
     * @var WorkContactServiceInterface
     */
    protected $contactClient;

    /**
     * @Inject
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var WxFinanceSDK
     */
    protected $sdk;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function handle(int $corpId, int $limit = 100): void
    {
        $this->msgClient = make(WorkMessageServiceInterface::class, [$corpId]);

        ## 查询微信配置
        $corp       = $this->corpClient->getCorpById($corpId, ['id', 'wx_corpid']);
        $corpConfig = $this->msgConfigClient->getWorkMessageConfigByCorpId($corpId, ['id', 'chat_rsa_key', 'chat_secret']);
        $rsa        = json_decode($corpConfig['chatRsaKey'], true);

        ## 获取会话数据
        $this->sdk = WxFinanceSDK::init([
            'corpid'       => $corp['wxCorpid'],
            'secret'       => $corpConfig['chatSecret'],
            'private_keys' => [
                $rsa['version'] => $rsa['privateKey'],
            ],
        ]);
        while (true) {
            $msgIndex = $this->msgClient->getWorkMessageIndexEndSeq();
            $chatData = $this->sdk->getDecryptChatData($msgIndex, $limit);
            if (empty($chatData)) {
                break;
            }

            $this->logger->info('wxMsgPull::stoneTag::会话消息拉取成功::seq.' . $msgIndex);
            $this->pullUpdate($corpId, $chatData);
            unset($chatData);
        }
    }

    /**
     * 企业聊天信息更新.
     * @param $corpId ...
     * @param array $chatData ...
     */
    protected function pullUpdate($corpId, array $chatData): void
    {
        ## 整理入库数据
        [$msgIndexData, $msgData] = $this->handleData($chatData, $corpId);

        ## 入库
        Db::beginTransaction();
        try {
            ## 入消息表
            $this->msgClient->createWorkMessages($msgData);
            $this->msgIndexClient->createWorkMessageIndices($msgIndexData);
            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            $this->logger->error('wxMsgPull::stoneTag::会话消息入库失败::' . $ex->getMessage(), ['msg' => $msgData, 'msgIndex' => $msgIndexData]);
        }

        $this->sensitiveMonitor($corpId, array_column($msgData, 'msg_id'));
    }

    /**
     * 敏感词监控入列队
     * @param int $corpId 企业ID
     * @param array $msgIds 消息IDS
     */
    protected function sensitiveMonitor(int $corpId, array $msgIds): void
    {
        $this->redis->rPush('sensitiveMonitor', json_encode([
            'corpId' => $corpId,
            'msgIds' => $msgIds,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * 整理入库数据.
     * @param array $data ...
     * @param int $corpId ...
     * @return array[] ...
     */
    protected function handleData(array $data, int $corpId): array
    {
        $msgIndexData = [];
        $msgData      = [];
        $allMsgType   = array_merge(MsgType::$otherType, MsgType::$fixedType);

        foreach ($data as $item) {
            ## 切换企业日志
            if ($item['action'] === 'switch') {
                $msgData[]                        = $this->switchData($corpId, $item);
                [$toListType, $tolistId, $roomId] = [0, [], 0];
            } else {
                [$toListType, $tolistId, $roomId] = $this->userIdFormat($item['roomid'], $item['tolist'][0]);
                ## 消息表
                $msgData[] = [
                    'corp_id'     => $corpId,
                    'seq'         => $item['seq'],
                    'msg_id'      => $item['msgid'],
                    'action'      => $this->actionIntval($item['action']),
                    'from'        => $item['from'],
                    'tolist'      => json_encode($item['tolist']),
                    'tolist_id'   => json_encode($tolistId),
                    'tolist_type' => $toListType,
                    'msg_type'    => $allMsgType[$item['msgtype']],
                    'content'     => json_encode($this->contentFormat($item), JSON_UNESCAPED_UNICODE),
                    'msg_time'    => (string) $item['msgtime'],
                    'wx_room_id'  => $item['roomid'],
                    'room_id'     => $roomId,
                ];
            }

            ## 消息检索表
            $fromId = isset($item['from']) ? $this->getEmployeeId($item['from']) : 0;
            if (! empty($fromId)) {
                $msgIndexData = $msgIndexData + $this->msgIndexParams($corpId, $fromId, $tolistId, $toListType);
            }
        }
        return [$this->diffMsgIndex($msgIndexData), $msgData];
    }

    /**
     * 检索表消息处理.
     * @param int $corpId ...
     * @param int $fromId ...
     * @param int $toType ...
     * @return array ...
     */
    protected function msgIndexParams(int $corpId, int $fromId, array $tolistId, int $toType): array
    {
        $resData    = [];
        $handleFunc = static function ($toId, $fromId) use ($corpId, $toType) {
            $msgIndexItem = [
                'corp_id' => $corpId,
                'to_id'   => $toId,
                'to_type' => $toType,
                'from_id' => $fromId,
            ];
            $msgIndexItem['flag'] = implode('_', [$msgIndexItem['from_id'], $msgIndexItem['to_type'], $msgIndexItem['to_id']]);
            return $msgIndexItem;
        };
        $toId = $tolistId[0] ?? 0;

        ## 原数据
        $fromData                   = $handleFunc($toId, $fromId);
        $resData[$fromData['flag']] = $fromData;

        ## 添加索引数据
        if ($toType === 0) {
            $toData                   = $handleFunc($fromId, $toId);
            $resData[$toData['flag']] = $toData;
        }

        return $resData;
    }

    /**
     * 切换企业日志.
     * @param int $corpId ...
     * @param array $data ...
     * @return array ...
     */
    protected function switchData(int $corpId, array $data): array
    {
        $fromId = $this->getEmployeeId($data['user']);
        return [
            'corp_id'     => $corpId,
            'seq'         => $data['seq'],
            'msg_id'      => $data['msgid'],
            'action'      => $this->actionIntval($data['action']),
            'from'        => $data['user'],
            'tolist'      => json_encode([]),
            'tolist_id'   => json_encode([$fromId]),
            'tolist_type' => 0,
            'msg_type'    => 0,
            'content'     => json_encode(['time' => $data['time']]),
            'msg_time'    => $data['time'],
            'wx_room_id'  => '',
            'room_id'     => 0,
        ];
    }

    /**
     * 消息action处理.
     * @param string $action ...
     * @return int ...
     */
    protected function actionIntval(string $action): int
    {
        return [
            'send'   => 0,
            'recall' => 1,
            'switch' => 2,
        ][$action];
    }

    /**
     * 用户ID处理.
     * @param string $wxRoomId ...
     * @param string $toUserId ...
     * @return array ...
     */
    protected function userIdFormat(string $wxRoomId, string $toUserId): array
    {
        // $toListType, $tolistId, $roomId
        $resData = [2, [], 0];

        ## 群
        if ($wxRoomId) {
            $roomData = $this->roomClient->getWorkRoomsByWxChatId([$wxRoomId], ['id']);
            if (! empty($roomData)) {
                $resData[2]    = $roomData[0]['id'];
                $resData[1][0] = $roomData[0]['id'];
            }
            return $resData;
        }

        ## 员工
        if (! in_array(substr($toUserId, 0, 2), ['wo', 'wm'])) {
            $resData[0]                            = 0;
            $employeeData                          = $this->employeeClient->getWorkEmployeeByWxUserId($toUserId, ['id']);
            empty($employeeData) || $resData[1][0] = $employeeData['id'];
            return $resData;
        }

        ## 客户
        $resData[0]                           = 1;
        $contactData                          = $this->contactClient->getWorkContactByWxExternalUserId($toUserId, ['id']);
        empty($contactData) || $resData[1][0] = $contactData['id'];
        return $resData;
    }

    /**
     * 处理消息内容.
     * @param array $data 消息数组
     * @throws \League\Flysystem\FileExistsException ...
     * @return array ...
     */
    protected function contentFormat(array $data): array
    {
        $msgType = $data['msgtype'];
        if ($msgType === 'docmsg') {
            $content = $data['doc'];
        } else {
            $content = $data[$msgType];
        }

        if (! isset($content['item'])) {
            return $this->sdkFileIdToOss($content, $msgType);
        }

        $items = array_map(function ($item) {
            return $this->sdkFileIdToOss(json_decode($item['content'], true), $item['type']);
        }, $content['item']);
        return ['item' => $items];
    }

    /**
     * 文件地址处理.
     * @param array $content ...
     * @param string $msgType ...
     * @throws \League\Flysystem\FileExistsException ...
     * @return array ...
     */
    protected function sdkFileIdToOss(array $content, string $msgType): array
    {
        ## 加类型
        $content['type'] = $msgType;

        if (! isset($content['sdkfileid'])) {
            return $content;
        }

        // 图片是jpg格式、语音是amr格式、视频是mp4格式、文件格式类型包括在消息体内，表情分为动图与静态图，在消息体内定义
        $file = false;
        switch ($msgType) {
            case 'image':
                $ext = 'jpg';
                break;
            case 'voice':
                $ext = 'amr';
                ## 转mp3
                $amrFile = $this->sdk->getMediaData($content['sdkfileid'], $ext);
                $file    = $this->ffmpegToMp3($amrFile);
                file_exists($amrFile->getRealPath()) && unlink($amrFile->getRealPath());
                break;
            case 'video':
                $ext = 'mp4';
                break;
            case 'file':
                $ext = $content['fileext'];
                break;
            case 'emotion':
                $ext = $content['type'] == 1 ? 'gif' : 'png';
                break;
            default:
                $ext = '';
        }
        $file || $file      = $this->sdk->getMediaData($content['sdkfileid'], $ext);
        $content['ossPath'] = date('Y/m/d/His/') . random_int(0, 100) . '/' . $file->getFilename();
//        $this->fileSystem->writeStream($content['ossPath'], fopen($file->getRealPath(), 'rb'));
        oss_up_queue([
            [$file->getRealPath(), $content['ossPath'], 1],
        ]);
//        unlink($file->getRealPath());

        return $content;
    }

    /**
     * 音频转MP3.
     * @param \SplFileInfo $file ...
     * @return \SplFileInfo ...
     */
    protected function ffmpegToMp3(\SplFileInfo $file): \SplFileInfo
    {
        try {
            ## 转mp3
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $audio  = $ffmpeg->open($file->getRealPath());

            $format  = new \FFMpeg\Format\Audio\Mp3();
            $mp3Path = $file->getPath() . '/' . md5((string) time()) . '.mp3';
            $audio->save($format, $mp3Path);
            return new \SplFileInfo($mp3Path);
        } catch (\Exception $e) {
            return $file;
        }
    }

    /**
     * 获取员工ID.
     * @param string $wxUserId ...
     * @return int ...
     */
    protected function getEmployeeId(string $wxUserId): int
    {
        $id                         = 0;
        $employeeData               = $this->employeeClient->getWorkEmployeeByWxUserId($wxUserId, ['id']);
        empty($employeeData) || $id = $employeeData['id'];
        return $id;
    }

    /**
     * 对比检索表数据.
     * @param array $data ...
     * @return array ...
     */
    protected function diffMsgIndex(array $data): array
    {
        $flags = array_column($data, 'flag');
        if (! empty($flags)) {
            $existData = $this->msgIndexClient->getWorkMessageIndicesByFlag(array_column($data, 'flag'), ['id', 'flag']);
            foreach ($existData as $item) {
                if (! isset($data[$item['flag']])) {
                    continue;
                }
                unset($data[$item['flag']]);
            }
        }
        return array_values($data);
    }
}
