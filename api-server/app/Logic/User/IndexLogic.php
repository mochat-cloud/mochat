<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\User;

use App\Constants\User\Status;
use App\Contract\RbacRoleServiceInterface;
use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 子账户管理-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @Inject
     * @var RbacUserRoleServiceInterface
     */
    protected $rbacUserRoleService;

    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $rbacRoleService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkEmployeeDepartmentServiceInterface
     */
    protected $workEmployeeDepartmentService;

    /**
     * @Inject
     * @var WorkDepartmentServiceInterface
     */
    protected $workDepartmentService;

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, array $user): array
    {
        ## 数据权限
        $userIds = $user['isSuperAdmin'] == 1 ? $this->getUserListByTenantId((int) $user['tenantId']) : $this->getEmployeeList($user);
        ## 统计用户状态
        $notEnabledNum = 0;
        $normalNum     = 0;
        $disableNum    = 0;
        $userList      = $this->userService->getUsersById($userIds, ['id', 'status']);
        if (! empty($userList)) {
            foreach ($userList as $value) {
                $value['status'] == Status::NOT_ENABLED && ++$notEnabledNum;
                $value['status'] == Status::NORMAL && ++$normalNum;
                $value['status'] == Status::DISABLE && ++$disableNum;
            }
        }

        ## 组织查询条件
        $where   = [];
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'id desc',
        ];
        empty($params['phone']) || $where[]           = ['phone', 'LIKE', '%' . $params['phone'] . '%'];
        $params['status'] == 'no' || $where['status'] = $params['status'];
        $where[]                                      = ['id', 'IN', $userIds];

        ## 查询数据
        $res = $this->userService->getUserList($where, ['*'], $options);

        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'notEnabledNum' => $notEnabledNum,
            'normalNum'     => $normalNum,
            'disableNum'    => $disableNum,
            'list'          => [],
        ];

        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];
        ## 获取用户的角色信息
        $userRoleList = $this->getUserRoleList(array_column($res['data'], 'id'));

        ## 处理数据
        foreach ($res['data'] as &$v) {
            $corpId          = empty($user['corpIds']) ? 0 : $user['corpIds'][0];
            $v['userId']     = $v['id'];
            $v['userName']   = $v['name'];
            $v['statusText'] = Status::getMessage($v['status']);
            $v['roleName']   = isset($userRoleList[$v['id']]) ? $userRoleList[$v['id']] : '';
            $v['department'] = $this->getDepartmentList((int) $v['id'], (int) $corpId);
            unset($v['id'], $v['name'], $v['password'], $v['position'], $v['updatedAt'], $v['deletedAt']);
        }
        $data['list'] = $res['data'];

        return $data;
    }

    /**
     * @param int $tenantId 租户ID
     * @return array 响应数组
     */
    private function getUserListByTenantId(int $tenantId): array
    {
        $list = $this->userService->getUsersByTenantId($tenantId, ['id']);
        return empty($list) ? [] : array_column($list, 'id');
    }

    /**
     * @param array $user 登录用户信息
     * @return array 响应数组
     */
    private function getEmployeeList(array $user): array
    {
        if ($user['dataPermission'] == 0) {
            $corpId = empty($user['corpIds']) ? 0 : $user['corpIds'][0];
            $data   = $this->workEmployeeService->getWorkEmployeesByCorpId((int) $corpId, ['log_user_id']);
        } else {
            $data = $this->workEmployeeService->getWorkEmployeesById($user['deptEmployeeIds'], ['log_user_id']);
        }
        return empty($data) ? [] : array_filter(array_unique(array_column($data, 'logUserId')));
    }

    /**
     * @param array $userIdArr 用户ID数组
     * @return array 响应数组
     */
    private function getUserRoleList(array $userIdArr): array
    {
        $userRoleList = $this->rbacUserRoleService->getRbacUserRolesByUserId($userIdArr, ['id', 'user_id', 'role_id']);
        if (empty($userRoleList)) {
            return [];
        }
        $roleIdArr = array_unique(array_column($userRoleList, 'roleId'));
        $roleList  = $this->rbacRoleService->getRbacRolesById($roleIdArr, ['id', 'name']);
        if (empty($roleList)) {
            return [];
        }
        $roleList     = array_column($roleList, 'name', 'id');
        $userRoleList = array_map(function ($userRole) use ($roleList) {
            return [
                'userId'   => $userRole['userId'],
                'roleName' => isset($roleList[$userRole['roleId']]) ? $roleList[$userRole['roleId']] : '',
            ];
        }, $userRoleList);

        return array_column($userRoleList, 'roleName', 'userId');
    }

    /**
     * @param int $userId 用户ID
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function getDepartmentList(int $userId, int $corpId): array
    {
        $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdLogUserId($corpId, $userId, ['id']);
        if (empty($employee)) {
            return [];
        }
        $employeeDepartments = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeId((int) $employee['id'], ['department_id']);
        if (empty($employeeDepartments)) {
            return [];
        }
        $departmentIdArr = array_column($employeeDepartments, 'departmentId');
        $departmentList  = $this->workDepartmentService->getWorkDepartmentsById($departmentIdArr, ['id', 'name']);
        return empty($departmentList) ? [] : array_map(function ($department) {
            return [
                'departmentId'   => $department['id'],
                'departmentName' => $department['name'],
            ];
        }, $departmentList);
    }
}
