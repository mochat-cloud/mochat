<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionInviteContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionWelcomeContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-详情
 * Class Store.
 * @Controller
 */
class Info extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 海报.
     * @Inject
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * 欢迎语.
     * @Inject
     * @var RoomFissionWelcomeContract
     */
    protected $roomFissionWelcomeService;

    /**
     * 群聊.
     * @Inject
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * 邀请用户.
     * @Inject
     * @var RoomFissionInviteContract
     */
    protected $roomFissionInviteService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomFission/info", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $this->validated($this->request->all());
        $id = (int) $this->request->input('id');
        ## 详情
        $fission = $this->roomFissionService->getRoomFissionById($id, ['id', 'official_account_id', 'active_name', 'end_time', 'target_count', 'new_friend', 'delete_invalid', 'receive_employees', 'auto_pass', 'created_at']);
        $fission['receiveEmployees'] = json_decode($fission['receiveEmployees'], true, 512, JSON_THROW_ON_ERROR);
        $poster = $this->roomFissionPosterService->getRoomFissionPosterByFissionId($id, ['id', 'cover_pic', 'avatar_show', 'nickname_show', 'nickname_color', 'qrcode_w', 'qrcode_h', 'qrcode_x', 'qrcode_y']);
        $poster['coverPic'] = file_full_url($poster['coverPic']);
        $welcome = $this->roomFissionWelcomeService->getRoomFissionWelcomeByFissionId($id, ['id', 'text', 'link_title', 'link_desc', 'link_pic']);
        $welcome['linkPic'] = file_full_url($welcome['linkPic']);
        $invite = $this->roomFissionInviteService->getRoomFissionInviteByFissionId($id, ['id', 'type', 'employees', 'choose_contact', 'text', 'link_title', 'link_desc', 'link_pic']);
        $invite['employees'] = json_decode($invite['employees'], true, 512, JSON_THROW_ON_ERROR);
        $invite['chooseContact'] = json_decode($invite['chooseContact'], true, 512, JSON_THROW_ON_ERROR);
        $invite['linkPic'] = file_full_url($invite['linkPic']);
        $rooms = $this->roomFissionRoomService->getRoomFissionRoomByFissionId($id, ['id', 'room_qrcode', 'room', 'room_max']);
        foreach ($rooms as $k => $v) {
            $rooms[$k]['roomQrcode'] = file_full_url($v['roomQrcode']);
            $rooms[$k]['room'] = json_decode($v['room'], true, 512, JSON_THROW_ON_ERROR);
        }

        $link = Url::getAuthRedirectUrl(8, $id, [
            'parent_union_id' => 0,
            'wx_user_id' => $id,
        ]);

        return ['fission' => $fission, 'poster' => $poster, 'welcome' => $welcome, 'rooms' => $rooms, 'invite' => $invite, 'link' => $link];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => 'id 必传',
        ];
    }
}
