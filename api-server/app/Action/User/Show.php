<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\User;

use App\Contract\RbacRoleServiceInterface;
use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 子账户管理- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

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
     * @RequestMapping(path="/user/show", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $userId = $this->request->input('userId');
        ## 查询数据
        $data = $this->userService->getUserById((int) $userId);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前账户不存在');
        }
        ## 角色信息
        $roleInfo = $this->findRoleInfo((int) $data['id']);

        $data['userId']     = $data['id'];
        $data['userName']   = $data['name'];
        $data['roleId']     = $roleInfo['roleId'];
        $data['roleName']   = $roleInfo['roleName'];
        $data['department'] = $this->getDepartmentList((int) $data['id'], (int) user()['corpIds'][0]);
        unset($data['id'], $data['name'], $data['position'], $data['loginTime'], $data['password'], $data['createdAt'], $data['updatedAt'], $data['deletedAt']);

        return $data;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'userId' => 'required | integer | min:0| bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'userId.required' => '用户ID 必填',
            'userId.integer'  => '用户ID 必需为整数',
            'userId.min'      => '用户ID 不可小于1',
        ];
    }

    /**
     * @param int $userId 用户ID
     * @return array 响应数组
     */
    private function findRoleInfo(int $userId): array
    {
        $data = [
            'roleId'   => 0,
            'roleName' => '',
        ];
        $userRole = $this->rbacUserRoleService->getRbacUserRoleByUserId($userId, ['id', 'role_id']);
        if (empty($userRole)) {
            return $data;
        }
        $role = $this->rbacRoleService->getRbacRoleById((int) $userRole['roleId'], ['id', 'name']);
        return empty($role) ? $data : [
            'roleId'   => $role['id'],
            'roleName' => $role['name'],
        ];
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
