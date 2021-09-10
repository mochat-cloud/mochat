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
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;
use Psr\Container\ContainerInterface;

/**
 * 标签建群-详情-客户.
 *
 * Class Show.
 * @Controller
 */
class ShowContact
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
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

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
     * @RequestMapping(path="/dashboard/roomTagPull/showContact", methods="get")
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
        $roomTagPull = $this->roomTagPullService->getRoomTagPullById((int) $params['id'], ['contact_num', 'wx_tid']);
        if ((int) $params['type'] === 1) {
            $contactData = $this->handleParamsContact($user, $params);
            $data = $this->getRoomTagPullContact($contactData);
            $data['contact_num'] = $roomTagPull['contactNum'];
            return $data;
        }

        if ((int) $params['type'] === 2) {
            $data = $this->getRoomTagPullEmployee($user, json_decode($roomTagPull['wxTid'], true, 512, JSON_THROW_ON_ERROR), $params);
            $data['contact_num'] = $roomTagPull['contactNum'];
            return $data;
        }
        return [];
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
            'type' => 'required | integer | bail',
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
            'type.required' => 'type 必填',
            'type.integer' => 'type 必须为整型',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParamsContact(array $user, array $params): array
    {
        $where['room_tag_pull_id'] = $params['id'];
        if (isset($params['contact_name']) && ! empty($params['contact_name'])) {
            $where[] = ['contact_name', 'LIKE', '%' . $params['contact_name'] . '%'];
        }
        if (isset($params['wx_user_id']) && ! empty($params['wx_user_id'])) {
            $where['wx_user_id'] = $params['wx_user_id'];
        }
        if (isset($params['send_status']) && is_numeric($params['send_status'])) {
            $where['send_status'] = $params['send_status'];
        }
        if (isset($params['is_join_room']) && is_numeric($params['is_join_room'])) {
            $where['is_join_room'] = $params['is_join_room'];
        }
        if (isset($params['room_id']) && ! empty($params['room_id'])) {
            $where['room_id'] = $params['room_id'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取标签建群客户详情.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomTagPullContact(array $params): array
    {
        $columns = ['id', 'contact_id', 'contact_name', 'employee_id', 'send_status', 'is_join_room', 'room_id', 'created_at'];
        $roomTagPullContactList = $this->roomTagPullContactService->getRoomTagPullContactList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomTagPullContactList['data']) ? $data : $this->handleDataContact($roomTagPullContactList);
    }

    /**
     * 获取标签建群员工详情.
     * @param array $data 数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomTagPullEmployee(array $user, array $data, array $params): array
    {
        $wxUserId = array_column($data, 'wxUserId');
        $userArr = array_count_values($wxUserId);
        foreach ($data as $k => $v) {
            $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserId((string) $user['corpIds'][0], $v['wxUserId'], ['name', 'avatar']);
            $data[$k]['task_num'] = $userArr[$v['wxUserId']];
            $data[$k]['name'] = $employee[0]['name'];
            $data[$k]['avatar'] = file_full_url($employee[0]['avatar']);
            $data[$k]['contact_num'] = $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdUserid((int) $params['id'], $v['wxUserId']);
            $data[$k]['invite_num'] = $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdUseridSendStatus((int) $params['id'], $v['wxUserId'], [1]);
            if (isset($params['wx_user_id']) && ! empty($params['wx_user_id']) && $v['wxUserId'] !== $params['wx_user_id']) {
                unset($data[$k]);
            }
            if (isset($params['is_send']) && is_numeric($params['is_send']) && $v['status'] !== $params['is_send']) {
                unset($data[$k]);
            }
            if (isset($params['task_type']) && $params['task_type'] > 0 && $params['task_type'] === 1 && $userArr[$v['wxUserId']] > 1) {
                unset($data[$k]);
            }
            if (isset($params['task_type']) && $params['task_type'] > 0 && $params['task_type'] === 2 && $userArr[$v['wxUserId']] === 1) {
                unset($data[$k]);
            }
            unset($data[$k]['tid']);
        }
        $list['list'] = array_unique($data, SORT_REGULAR);
        return $list;
    }

    /**
     * 数据处理.
     * @param array $roomTagPullContactList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleDataContact(array $roomTagPullContactList): array
    {
        $list = [];
        foreach ($roomTagPullContactList['data'] as $key => $val) {
            $contact = $this->workContactService->getWorkContactById($val['contactId'], ['avatar']);
            $employees = $this->workEmployeeService->getWorkEmployeeById($val['employeeId'], ['name']);
            $room = $this->workRoomService->getWorkRoomById($val['roomId'], ['name']);
            $list[$key] = [
                'avatar' => file_full_url($contact['avatar']),
                'contact_name' => $val['contactName'],
                'employee_name' => $employees['name'],
                'send_status' => $val['sendStatus'],
                'room_name' => $room['name'],
                'is_join_room' => $val['isJoinRoom'],
            ];
        }

        $data['page']['total'] = $roomTagPullContactList['total'];
        $data['page']['totalPage'] = $roomTagPullContactList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
