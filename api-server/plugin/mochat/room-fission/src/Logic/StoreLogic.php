<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\Utils\Url;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionInviteContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionWelcomeContract;

/**
 * 群裂变-增加.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 海报.
     * @Inject()
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * 欢迎语.
     * @Inject()
     * @var RoomFissionWelcomeContract
     */
    protected $roomFissionWelcomeService;

    /**
     * 群聊.
     * @Inject()
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * 邀请用户.
     * @Inject()
     * @var RoomFissionInviteContract
     */
    protected $roomFissionInviteService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $params = $this->handleParam($user, $params);
        ## 创建活动
        $id = $this->createRoomFission((int) $user['corpIds'][0], $params);

        return [$id];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $data['fission'] = [
            'official_account_id' => $params['fission']['official_account_id'],
            'active_name' => $params['fission']['active_name'],
            'end_time' => $params['fission']['end_time'],
            'target_count' => (int) $params['fission']['target_count'],
            'new_friend' => (int) $params['fission']['new_friend'],
            'delete_invalid' => (int) $params['fission']['delete_invalid'],
            'receive_employees' => json_encode($params['fission']['receive_employees'], JSON_THROW_ON_ERROR),
            'auto_pass' => (int) $params['fission']['auto_pass'],
            'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
            'corp_id' => $user['corpIds'][0],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        ##欢迎语
        if (! empty($params['welcome']['link_pic'])) {
            $params['welcome']['link_pic'] = File::uploadBase64Image($params['welcome']['link_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }

        $data['welcome'] = [
            'text' => $params['welcome']['text'],
            'link_title' => $params['welcome']['link_title'],
            'link_desc' => $params['welcome']['link_desc'],
            'link_pic' => $params['welcome']['link_pic'],
            'link_wx_url' => $params['welcome']['link_pic'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        ##海报
        $data['poster'] = [
            'cover_pic' => File::uploadBase64Image($params['poster']['cover_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
            'avatar_show' => (int) $params['poster']['avatar_show'],
            'nickname_show' => (int) $params['poster']['nickname_show'],
            'nickname_color' => $params['poster']['nickname_color'],
            'qrcode_w' => $params['poster']['qrcode_w'],
            'qrcode_h' => $params['poster']['qrcode_h'],
            'qrcode_x' => $params['poster']['qrcode_x'],
            'qrcode_y' => $params['poster']['qrcode_y'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        ##群聊
        $data['rooms'] = $params['rooms'];
        ##客户参与
        if (! empty($params['invite']['link_pic'])) {
            $params['invite']['link_pic'] = File::uploadBase64Image($params['invite']['link_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }
        $data['invite'] = [
            'type' => (int) $params['invite']['type'],
            'employees' => empty($params['invite']['employees']) ? '{}' : json_encode($params['invite']['employees'], JSON_THROW_ON_ERROR),
            'choose_contact' => empty($params['invite']['choose_contact']) ? '{}' : json_encode($params['invite']['choose_contact'], JSON_THROW_ON_ERROR),
            'text' => $params['invite']['text'],
            'link_title' => $params['invite']['link_title'],
            'link_desc' => $params['invite']['link_desc'],
            'link_pic' => $params['invite']['link_pic'],
            //            'wx_link_pic' => $invite_url['url'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $data;
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return int 响应数值
     */
    private function createRoomFission($corpId, array $params): int
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->roomFissionService->createRoomFission($params['fission']);
            ## 创建海报
            $params['poster']['fission_id'] = $id;
            $this->roomFissionPosterService->createRoomFissionPoster($params['poster']);
            ## 欢迎语
            $params['welcome']['fission_id'] = $id;
            $templateId = $this->handleWelcome($corpId, (int) $id, $params['welcome']);
            $params['welcome']['template_id'] = $templateId;
            $this->roomFissionWelcomeService->createRoomFissionWelcome($params['welcome']);
            ## 群聊
            foreach ($params['rooms'] as $k => $v) {
                $params['rooms'][$k]['fission_id'] = $id;
                $params['rooms'][$k]['room_qrcode'] = File::uploadBase64Image($v['room_qrcode'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
                $params['rooms'][$k]['room'] = json_encode($v['room'], JSON_THROW_ON_ERROR);
            }
            $this->roomFissionRoomService->createRoomFissionRooms($params['rooms']);
            ## 邀请客户
            $params['invite']['fission_id'] = $id;
            $this->roomFissionInviteService->createRoomFissionInvite($params['invite']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '活动创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return $id;
    }

    /**
     * 处理欢迎语
     *
     * @param int $corpId
     * @param int $fissionId
     * @param array $welcome
     * @return string
     */
    private function handleWelcome(int $corpId, int $fissionId, array $welcome): string
    {
        $url = Url::getAuthRedirectUrl(8, $fissionId, ['parent_union_id' => '', 'wx_user_id' => '']);
        $easyWeChatParams['text']['content'] = $welcome['text'];
        $easyWeChatParams['link'] = [
            'title' => $welcome['link_title'],
            'url' => $url
        ];

        if (isset($welcome['link_pic']) && !empty($welcome['link_pic'])) {
            $easyWeChatParams['link']['picurl'] = $welcome['link_pic'];
        }

        if (isset($welcome['desc']) && !empty($welcome['desc'])) {
            $easyWeChatParams['link']['desc'] = $welcome['desc'];
        }

        ## EasyWeChat添加入群欢迎语素材
        $template = $this->wxApp($corpId, 'contact')->external_contact_message_template->create($easyWeChatParams);
        if ($template['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '添加入群欢迎语素材失败' . $template['errmsg']);
        }
        return $template['template_id'];
    }
}
