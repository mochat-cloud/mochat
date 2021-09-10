<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;
use Psr\Container\ContainerInterface;

/**
 * 标签建群-详情.
 *
 * Class Show.
 * @Controller
 */
class Show
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomTagPullContract
     */
    protected $roomTagPullService;

    /**
     * @Inject
     * @var RoomTagPullContactContract
     */
    protected $roomTagPullContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @var int
     */
    protected $perPage;

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
     * @var CorpContract
     */
    protected $corpService;

    /**
     * Show constructor.
     */
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
     * @RequestMapping(path="/dashboard/roomTagPull/show", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 验证参数
        $params = $this->request->all();
        $this->validated($params);

        return $this->getRoomTagPull($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动id 必填',
            'id.integer' => '活动id 必须为整型',
        ];
    }

    /**
     * 获取标签建群详情.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomTagPull(array $user, array $params): array
    {
        $roomTagPull = $this->roomTagPullService->getRoomTagPullById((int) $params['id'], ['employees', 'rooms', 'wx_tid']);
        $employees = $this->workEmployeeService->getWorkEmployeesById(explode(',', $roomTagPull['employees']), ['name', 'avatar', 'wx_user_id']);
        foreach ($employees as $key => $val) {
            $employees[$key]['avatar'] = file_full_url($val['avatar']);
        }
        $rooms = json_decode($roomTagPull['rooms'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($rooms as $k => $v) {
            $room = $this->workRoomService->getWorkRoomById($v['id'], ['id', 'wx_chat_id', 'name', 'owner_id', 'room_max']);
            $rooms[$k]['contact_num'] = $this->workContactRoomService->countWorkContactRoomsByRoomIdContact((int) $v['id']);
            $rooms[$k]['room_max'] = $room['roomMax'];
            $rooms[$k]['image'] = file_full_url($v['image']);
            unset($rooms[$k]['num'], $rooms[$k]['image'], $rooms[$k]['wx_image']);
        }
        $send_num = 0;
        $noSendNum = 0;
        foreach (json_decode($roomTagPull['wxTid'], true, 512, JSON_THROW_ON_ERROR) as $tid) {
            if ($tid['status'] !== 0) {
                ++$send_num;
            }
            if ($tid['status'] === 0) {
                ++$noSendNum;
            }
        }
        return [
            'employees' => $employees,
            'rooms' => $rooms,
            'join_room_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdJoinRoom((int) $params['id'], 1),
            'no_join_room_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdJoinRoom((int) $params['id'], 0),
            'invite_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdSendStatus((int) $params['id'], [1]),
            'no_invite_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdSendStatus((int) $params['id'], [0]),
            'send_num' => $send_num,
            'no_send_num' => $noSendNum,
        ];
    }
}
