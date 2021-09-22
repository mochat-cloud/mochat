<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;

/**
 * 任务宝 - 获取客户邀请数据.
 *
 * Class Store.
 * @Controller
 */
class InviteData extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * Statistics constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService, WorkEmployeeContract $workEmployeeService)
    {
        $this->request = $request;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
        $this->workEmployeeService = $workEmployeeService;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workFission/inviteData", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        ##处理参数
        $params['page'] = $this->request->input('page', 1);
        $params['perPage'] = $this->request->input('perPage', 10000);
        $data = $this->handleParams($params);

        return $this->getUserList($data);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $where[] = ['level', '>', '0'];
        if (! empty($params['fission_ids'])) {
            $where[] = ['fission_id', 'in', json_decode($params['fission_ids'], true)];
        }
        if (! empty($params['nickname'])) {
            $where[] = ['nickname', 'LIKE', '%' . $params['nickname'] . '%'];
        }
        if (! empty($params['employee'])) {
            $where['employee'] = $params['employee'];
        }
        if (! empty($params['start_time'])) {
            $where[] = ['created_at', '>', $params['start_time']];
            $where[] = ['created_at', '<', $params['end_time']];
        }
        if (isset($params['status']) && is_numeric($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['loss']) && is_numeric($params['loss'])) {
            $where['loss'] = $params['loss'];
        }

        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    private function getUserList($params): array
    {
        $columns = ['id', 'fission_id', 'union_id', 'nickname', 'avatar', 'employee', 'contact_superior_user_parent', 'level', 'loss', 'status', 'invite_count', 'created_at'];
        $userList = $this->workFissionContactService->getWorkFissionContactList($params['where'], $columns, $params['options']);

        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => [],
        ];

        return empty($userList['data']) ? $data : $this->handleData($userList);
    }

    /**
     * 数据处理.
     * @param array $userList 客户数据
     * @return array 响应数组
     */
    private function handleData(array $userList): array
    {
        $list = [];
        foreach ($userList['data'] as $key => $val) {
            $contact = $this->workContactService->getWorkContactByCorpIdUnionId(user()['corpIds'][0], $val['unionId'], ['id']);
            $employeeId = 0;
            if (! empty($contact)) {
                $employee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId(user()['corpIds'][0], $contact['id'], ['employee_id']);
                $employeeId = empty($employee) ? 0 : $employee['employeeId'];
            }
            $list[$key] = [
                'id' => $val['id'],
                'nickname' => $val['nickname'],
                'avatar' => $val['avatar'],
                'active_name' => $this->getFissionName((int) $val['fissionId']),
                'employees' => empty($val['employee']) ? '未知' : $this->getEmployee($val['employee']),
                'created_at' => $val['createdAt'],
                'loss' => $val['loss'] == 1 ? '已流失' : '未流失',
                'level' => $val['level'],
                'status' => $val['status'] == 1 ? '已完成' : '未完成',
                'invite_count' => $val['inviteCount'],
                'contact_id' => empty($contact) ? 0 : $contact['id'],
                'employee_id' => $employeeId,
            ];
        }
        $data['page']['total'] = $userList['total'];
        $data['page']['totalPage'] = $userList['last_page'];
        $data['list'] = $list;

        return $data;
    }

    /**
     * 活动名称.
     */
    private function getFissionName(int $id): string
    {
        $info = $this->workFissionService->getWorkFissionById($id);
        return $info['activeName'];
    }

    /**
     * 所属成员.
     */
    private function getEmployee(string $wxUserId): string
    {
        $info = $this->workEmployeeService->getWorkEmployeeByWxUserIdCorpId($wxUserId, (int) user()['corpIds'][0]);
        return $info['name'] ?? '未知';
    }
}
