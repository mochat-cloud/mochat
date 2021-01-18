<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkDepartment;

use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 成员部门管理-根据手机号匹配成员部门下拉列表.
 *
 * Class SelectByPhone.
 * @Controller
 */
class SelectByPhone extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkDepartmentServiceInterface
     */
    protected $workDepartmentService;

    /**
     * @Inject
     * @var WorkEmployeeDepartmentServiceInterface
     */
    protected $workEmployeeDepartmentService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @RequestMapping(path="/workDepartment/selectByPhone", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $phone = $this->request->input('phone');
        $type  = $this->request->input('type', 1);

        ## 根据手机号查询成员通讯录
        $employeeList = $this->workEmployeeService->getWorkEmployeesByMobile($phone, ['id', 'corp_id']);
        if (empty($employeeList)) {
            return [];
        }
        $employeeList  = array_column($employeeList, 'id', 'corpId');
        $employeeIdArr = ($type == 1) ? array_values($employeeList) : (
            isset($employeeList[$user['corpIds'][0]]) ? [$employeeList[$user['corpIds'][0]]] : []
        );
        if (empty($employeeIdArr)) {
            return [];
        }
        ## 查询成员-部门关联表
        $employeeDepartmentList = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeIds($employeeIdArr, ['id', 'department_id']);
        if (empty($employeeDepartmentList)) {
            return [];
        }
        ## 查询部门信息
        $departmentList = $this->workDepartmentService->getWorkDepartmentsById(array_column($employeeDepartmentList, 'departmentId'), ['id', 'corp_id', 'name']);

        return empty($departmentList) ? [] : array_map(function ($department) {
            return [
                'corpId'             => $department['corpId'],
                'workDepartmentId'   => $department['id'],
                'workDepartmentName' => $department['name'],
            ];
        }, $departmentList);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'phone' => 'required | string | size:11 | bail',
            'type'  => 'integer | in:1,2, | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'phone.required' => '手机号码 必填',
            'phone.string'   => '手机号码 必需为字符串',
            'phone.size'     => '手机号码 字符串长度为固定值：11',
            'type.integer'   => '数据类型 必需为整数',
            'type.in'        => '数据类型 值必须在列表内：[1,2]',
        ];
    }
}
