<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkEmployeeDepartment;

use App\Constants\WorkEmployee\Status;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 部门下的成员列表.
 *
 * Class MemberIndex
 * @Controller
 */
class MemberIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * 部门 - 成员 关联.
     * @Inject
     * @var WorkEmployeeDepartmentServiceInterface
     */
    private $employeeDepartmentService;

    /**
     * 部门.
     * @Inject
     * @var WorkDepartmentServiceInterface
     */
    private $departmentService;

    /**
     * 成员.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $employeeService;

    /**
     * @RequestMapping(path="/workEmployeeDepartment/memberIndex", methods="GET")
     * @return array 响应数据
     */
    public function handle()
    {
        //参数
        $params['departmentIds'] = $this->request->input('departmentIds');
        //验证
        $this->validated($params);

        //获取信息
        return $this->getEmployeeInfo($params['departmentIds']);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'departmentIds' => 'required|string',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'departmentIds.required' => '部门id必传',
        ];
    }

    /**
     * 获取成员信息.
     * @param $departmentIds
     * @return array
     */
    private function getEmployeeInfo($departmentIds)
    {
        //查询已激活成员信息
        $employeeInfo = $this->employeeService->getWorkEmployeesByCorpIdStatus(
            (int) user()['corpIds'][0],
            (int) Status::ACTIVE,
            ['id', 'name']
        );
        if (empty($employeeInfo)) {
            return [];
        }
        $employeeInfo      = array_column($employeeInfo, null, 'id');
        $activeEmployeeIds = array_column($employeeInfo, 'id');

        $departmentIds = explode(',', $departmentIds);
        //获取部门、成员关联信息
        $employeeDepartment = $this->employeeDepartmentService
            ->getWorkEmployeeDepartmentsByOtherId($departmentIds, $activeEmployeeIds, ['employee_id', 'department_id']);

        if (empty($employeeDepartment)) {
            return [];
        }

        //根据部门id获取部门名称
        $departmentInfo = $this->departmentService->getWorkDepartmentsById($departmentIds, ['id', 'name']);
        if (! empty($departmentInfo)) {
            $departmentInfo = array_column($departmentInfo, null, 'id');
        }

        foreach ($employeeDepartment as &$val) {
            $val['departmentName'] = '';
            $val['employeeName']   = '';
            if (isset($departmentInfo[$val['departmentId']])) {
                $val['departmentName'] = $departmentInfo[$val['departmentId']]['name'];
            }
            if (isset($employeeInfo[$val['employeeId']])) {
                $val['employeeName'] = $employeeInfo[$val['employeeId']]['name'];
            }
        }
        unset($val);

        return $employeeDepartment;
    }
}
