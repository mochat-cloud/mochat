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
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-客户数据
 * Class ShowContact.
 * @Controller
 */
class ShowContact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 群聊.
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * 邀请用户.
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

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

    /**
     * @var int
     */
    protected $perPage;

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
     * @RequestMapping(path="/dashboard/roomFission/showContact", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $this->validated($this->request->all());
        ## 验证参数
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'id' => $this->request->input('id'),
            'name' => $this->request->input('name'),
            'start_time' => $this->request->input('start_time'),
            'end_time' => $this->request->input('end_time'),
            'loss_status' => $this->request->input('loss_status'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        $data = $this->handleParams($params);

        return $this->getContactList($user, $data);
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

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $where['fission_id'] = $params['id'];
        if (isset($params['name'])) {
            $where[] = ['nickname', 'LIKE', '%' . $params['name'] . '%'];
        }
        if (! empty($params['start_time'])) {
            $where[] = ['created_at', '>', $params['start_time']];
            $where[] = ['created_at', '<', $params['end_time']];
        }
        if (isset($params['loss_status']) && is_numeric($params['loss_status'])) {
            $where['loss'] = $params['loss_status'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getContactList(array $user, array $params): array
    {
        $columns = ['id', 'union_id', 'nickname', 'avatar', 'employee', 'invite_count', 'loss', 'status', 'receive_status', 'room_id', 'join_status', 'write_off', 'contact_id'];
        $contactList = $this->roomFissionContactService->getRoomFissionContactList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data : $this->handleData($user, $contactList);
    }

    /**
     * 数据处理.
     * @param array $contactList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $user, array $contactList): array
    {
        $list = [];
        foreach ($contactList['data'] as $key => $val) {
            $joinTime = '-';
            $outTime = '-';
            if ($val['joinStatus'] === 1) {
                $roomContact = $this->workContactRoomService->getWorkContactRoomsByRoomIdUnion($val['roomId'], $val['unionId'], ['join_time', 'out_time']);
                $joinTime = $roomContact['joinTime'];
                if ($val['loss'] === 1) {
                    $outTime = $roomContact['outTime'];
                }
            }
            $employeeId = 0;
            if ($val['contactId'] > 0) {
                $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($user['corpIds'][0], $val['contactId'], ['employee_id']);
                $employeeId = empty($contactEmployee) ? 0 : $contactEmployee['employeeId'];
            }
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['nickname'],
                'avatar' => file_full_url($val['avatar']),
                'type' => $employeeId === 0 ? '非企业客户' : '企业客户',
                'join_time' => $joinTime,
                'status' => $val['status'] === 1 ? '已完成' : '未完成',
                'loss' => $val['loss'] === 1 ? '已流失' : '未流失',
                'out_time' => $outTime,
                'invite_count' => $val['inviteCount'],
                'receive_status' => $val['receiveStatus'],
                'write_off' => $val['writeOff'],
                'contact_id' => $val['contactId'],
                'employee_id' => $employeeId,
            ];
        }
        $data['page']['total'] = $contactList['total'];
        $data['page']['totalPage'] = $contactList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
