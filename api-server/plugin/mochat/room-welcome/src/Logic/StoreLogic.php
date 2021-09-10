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
use League\Flysystem\FileExistsException;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;

/**
 * 入群欢迎语-增加.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomWelcomeContract
     */
    protected $roomWelcomeService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws FileExistsException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $params = $this->handleParam($user, $params);
        ## 创建欢迎语
        $this->createRoomWelcome($params);

        return [];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws FileExistsException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
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
                    $pic_url = '';
                    if ($complex['miniprogram']['pic']) {
                        $file = $this->handlePic($user, $complex['miniprogram']['pic'], 2);
                        $complex['miniprogram']['pic'] = $file['pic'];
                        $complex['miniprogram']['pic_media_id'] = $file['pic_url'];
                        $pic_url = $file['pic_url'];
                    }
                    $easyWeChatParams['text']['content'] = $text;
                    $easyWeChatParams['miniprogram'] = ['title' => $complex['miniprogram']['title'], 'pic_media_id' => $pic_url, 'appId' => $complex['miniprogram']['appid'], 'page' => $complex['miniprogram']['page']];
                }
                $msgComplex = $complex['miniprogram'];
                break;
        }
        ##EasyWeChat添加入群欢迎语素材
        $template = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message_template->create($easyWeChatParams);
        if ($template['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '添加入群欢迎语素材失败' . $template['errmsg']);
        }
        return [
            'corp_id' => $params['corp_id'],
            'msg_text' => $params['msg_text'],
            'complex_type' => empty($msgComplex) ? '' : $complex['type'],
            'msg_complex' => json_encode($msgComplex, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'complex_template_id' => $template['template_id'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 图片处理.
     * @param $user
     * @param $file
     * @throws FileExistsException
     */
    private function handlePic($user, $file, int $type = 1): array
    {
        $res = ['pic' => '', 'pic_url' => ''];
        $file = File::uploadBase64Image($file, 'image/roomWelcome/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'); //将Base64图片转换为本地图片并保存
        $localFile = File::download(file_full_url($file), $file);
        $res['pic'] = $file;
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
     * 创建欢迎语.
     * @param array $params 参数
     * @return int 响应数值
     */
    private function createRoomWelcome(array $params): int
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建欢迎语
            $id = $this->roomWelcomeService->createRoomWelcome($params);
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
