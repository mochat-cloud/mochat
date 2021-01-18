<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Role;

use App\Contract\UserServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 角色-成员列表.
 *
 * Class ShowEmployeeLogic
 */
class ShowEmployeeLogic
{
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
     * @Inject
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 通过角色id获取成员
        $columns = ['id', 'name', 'mobile', 'email', 'log_user_id'];
        $where   = [
            'roleId' => $params['roleId'],
            'corpId' => $user['corpIds'][0],
        ];
        $options = [
            'page'    => $params['page'],
            'perPage' => $params['perPage'],
        ];
        $employee = $this->workEmployeeService->getWorkEmployeesByRoleId($where, $options, $columns);

        ## 数据处理
        return $this->handleData($employee);
    }

    /**
     * 组织返回数据.
     * @param array $employee 成员信息
     * @return array
     */
    private function handleData(array $employee)
    {
        $data = [
            'page' => [
                'perPage'   => 10,
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        if (empty($employee['data'])) {
            return $data;
        }

        ## 处理数据
        $list = [];
        foreach ($employee['data'] as $key => $v) {
            $list[$key] = [
                'employeeId'   => $v['id'],
                'employeeName' => $v['name'],
                'phone'        => $v['mobile'],
                'email'        => $v['email'],
                'department'   => $this->getEmployeeDepartmentByEmployeeId($v['id']),
            ];
        }

        ## 分页数据
        $data['page']['total']     = $employee['total'];
        $data['page']['totalPage'] = $employee['last_page'];
        $data['list']              = $list;

        return $data;
    }

    /**
     * 根据成员获取所在部门.
     * @param int $employeeId 成员id
     */
    private function getEmployeeDepartmentByEmployeeId(int $employeeId): string
    {
        ## 根据成员id获取成员部门id
        $departmentIds = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeId($employeeId, ['department_id']);
        if (empty($departmentIds)) {
            return '';
        }
        $departmentArr = array_column($departmentIds, 'departmentId');

        ## 根据部门ID获取部门数据
        $depart = $this->workDepartmentService->getWorkDepartmentsById($departmentArr, ['name']);
        if (empty($depart)) {
            return '';
        }
        $department = array_column($depart, 'name');
        return implode(',', $department);
    }
}
