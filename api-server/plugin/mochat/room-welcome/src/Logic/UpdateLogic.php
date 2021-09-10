<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomWelcome\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;

/**
 * 入群欢迎语-更新.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    use AppTrait;

    /**
     * @var RoomWelcomeContract
     */
    protected $roomWelcomeService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(CorpContract $corpService, RoomWelcomeContract $roomWelcomeService)
    {
        $this->corpService = $corpService;
        $this->roomWelcomeService = $roomWelcomeService;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws \JsonException
     * @throws \League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $data = $this->handleParam($user, $params);
        ## 创建欢迎语
        $this->updateRoomWelcome((int) $params['id'], $data);

        return [];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException|\League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $info = $this->roomWelcomeService->getRoomWelcomeById((int) $params['id']);
        $complex = json_encode($params['msg_complex'], JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT);
        $complex = json_decode($complex, true, 512, JSON_THROW_ON_ERROR);
        $msgComplex = '';
        $text = '';
        $easyWeChatParams = [];
        if (! empty($params['msg_text'])) {
            $text = str_replace('[用户昵称]', '%NICKNAME%', $params['msg_text']);
        }
        if (! empty($params['msg_text']) && empty($complex['image']['pic']) && empty($complex['link']['url']) && empty($complex['miniprogram']['title'])) {
            $easyWeChatParams['text']['content'] = $text;
        }
        ## 图片处理 添加入群欢迎语素材
        switch ($complex['type']) {
            case 'image':
                if (! empty($complex['image']['pic'])) {
                    $file = $this->handlePic($user, $complex['image']['pic']);
                    $complex['image']['pic'] = $file['pic'];
                    $complex['image']['pic_url'] = $file['pic_url'];
                    $easyWeChatParams['text']['content'] = $text;
                    $easyWeChatParams['image']['pic_url'] = $file['pic_url'];
                }
                $msgComplex = $complex['image'];
                break;
            case 'link':
                if (! empty($complex['link']['url'])) {
                    $pic_url = '';
                    if ($complex['link']['pic']) {
                        $file = $this->handlePic($user, $complex['link']['pic']);
                        $complex['link']['pic'] = $file['pic'];
                        $complex['link']['pic_url'] = $file['pic_url'];
                        $pic_url = $file['pic_url'];
                    }
                    $easyWeChatParams['text']['content'] = $text;
                    $easyWeChatParams['link'] = ['title' => $complex['link']['title'], 'picurl' => $pic_url, 'desc' => $complex['link']['desc'], 'url' => $complex['link']['url']];
                }
                $msgComplex = $complex['link'];
                break;
            case 'miniprogram':
                if (! empty($complex['miniprogram']['title'])) {
                    $mediaId = '';
                    if ($complex['miniprogram']['pic']) {
                        $file = $this->handlePic($user, $complex['miniprogram']['pic'], 2);
                        $complex['miniprogram']['pic'] = $file['pic'];
                        $complex['miniprogram']['pic_url'] = $file['pic_url'];
                        $mediaId = $file['pic_url'];
                    }
                    $easyWeChatParams['text']['content'] = $text;
                    $easyWeChatParams['miniprogram'] = ['title' => $complex['miniprogram']['title'], 'pic_media_id' => $mediaId, 'appId' => $complex['miniprogram']['appid'], 'page' => $complex['miniprogram']['page']];
                }
                $msgComplex = $complex['miniprogram'];
                break;
        }
        ##EasyWeChat添加入群欢迎语素材
        $template = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message_template->update($info['complexTemplateId'], $easyWeChatParams);
        if ($template['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '修改入群欢迎语素材失败' . $template['errmsg']);
        }
        return [
            'corp_id' => $params['corp_id'],
            'msg_text' => $params['msg_text'],
            'complex_type' => $complex['type'],
            'msg_complex' => json_encode($msgComplex, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * 图片处理.
     * @param $user
     * @param $file
     * @throws \League\Flysystem\FileExistsException
     */
    private function handlePic($user, $file, int $type = 1): array
    {
        $res = ['pic' => '', 'pic_url' => ''];
        $file = File::uploadBase64Image($file, 'image/roomWelcome/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'); //将Base64图片转换为本地图片并保存
        $res['pic'] = $file;
        $localFile = File::download(file_full_url($file), $file);
        if ($type === 1) {
            ##EasyWeChat上传图片
            $uploadRes = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
            if ($uploadRes['errcode'] !== 0) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '上传图片失败');
            }
            $res['pic_url'] = $uploadRes['url'];
        } else {
            ##EasyWeChat上传临时素材
            $uploadRes = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadFile($localFile);
            if ((int) $uploadRes['errcode'] !== 0) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '上传临时素材失败');
            }
            $res['pic_url'] = $uploadRes['media_id'];
        }

        return $res;
    }

    /**
     * 更新欢迎语.
     * @param array $params 参数
     * @return int 响应数值
     */
    private function updateRoomWelcome(int $id, array $params): int
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 更新欢迎语
            $this->roomWelcomeService->updateRoomWelcomeById($id, $params);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '欢迎语创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '欢迎语创建失败');
        }
        return $id;
    }
}
