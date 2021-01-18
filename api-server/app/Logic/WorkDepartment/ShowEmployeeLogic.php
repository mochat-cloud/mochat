<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkDepartment;

use App\Contract\RbacRoleServiceInterface;
use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 部门人员-列表.
 *
 * Class ShowEmployeeLogic
 */
class ShowEmployeeLogic
{
    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $rbacRoleService;

    /**
     * @Inject
     * @var RbacUserRoleServiceInterface
     */
    protected $rbacUserRoleService;

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
     * @var int
     */
    protected $corpId;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);
        ## 查询数据
        return $this->getUserDepartment($params);
    }

    /**
     * 处理请求参数.
     *
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 列表查询条件
        $this->corpId     = $user['corpIds'][0];
        $where['corp_id'] = $this->corpId;

        ## 根据部门id搜索
        if (! empty($params['departmentId'])) {
            ## 根据部门获取成员id
            $employeeIds = $this->getEmployeesByDepartmentId((int) $params['departmentId']);
            $where['id'] = $employeeIds;
        }

        ## 分页信息
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'updated_at desc',
        ];

        return $data = [
            'where'   => $where,
            'options' => $options,
        ];
    }

    /**
     * 获取成员列表.
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getUserDepartment(array $params): array
    {
        ## 分页查询成员表数据表
        $columns      = ['id', 'log_user_id', 'name', 'mobile'];
        $employeeList = $this->workEmployeeService->getWorkEmployeeList($params['where'], $columns, $params['options']);

        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        return empty($employeeList['data']) ? $data : $this->handleData($data, $employeeList);
    }

    /**
     * 组织响应数据.
     *
     * @param array $data 请求参数
     * @param array $employeeList ...
     * @return array 响应数组
     */
    private function handleData(array $data, array $employeeList): array
    {
        ## 分页数据
        $data['page']['total']     = $employeeList['total'];
        $data['page']['totalPage'] = $employeeList['last_page'];

        ## 数据处理
        $data['list'] = array_map(function ($val) {
            $role = $this->getRoleNameByUserId($val['logUserId']);
            return [
                'employeeId'   => $val['id'],
                'employeeName' => $val['name'],
                'phone'        => $val['mobile'],
                'roleName'     => ! empty($role) ? $role['name'] : '',
            ];
        }, $employeeList['data']);

        return $data;
    }

    /**
     * 根据用户id-获取角色名称.
     *
     * @param int $userId 用户id
     * @return array 相应数组
     */
    private function getRoleNameByUserId(int $userId): array
    {
        ## 根据用户id获取角色id
        $userRole = $this->rbacUserRoleService->getRbacUserRoleByUserId($userId, ['role_id']);
        if (empty($userRole)) {
            return [];
        }

        ## 根据角色id获取角色名称
        $role = $this->rbacRoleService->getRbacRoleById($userRole['roleId'], ['name']);
        if (empty($role)) {
            return [];
        }

        return $role;
    }

    /**
     * 根据部门id-获取成员.
     *
     * @param int $departmentId 部门id
     */
    private function getEmployeesByDepartmentId(int $departmentId): array
    {
        $columns  = ['employee_id'];
        $employee = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByDepartmentIds([$departmentId], $columns);
        return ! empty($employee) ? array_column($employee, 'employeeId') : [];
    }
}
