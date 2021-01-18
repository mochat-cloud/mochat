<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Role;

use App\Contract\RbacRoleServiceInterface;
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
 * 删除 - 动作.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * 角色.
     * @Inject
     * @var RbacRoleServiceInterface
     */
    private $roleService;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $this->validated($this->request->all(), 'destroy');
        $id = (int) $this->request->input('roleId');

        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        if (! empty($this->getEmployeesCountByRoleId($id, $user['corpIds'][0]))) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '角色下有成员，不能删除角色');
        }
        ## 删除
        $client = $this->container->get(RbacRoleServiceInterface::class);
        $res    = $client->deleteRbacRole($id);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
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
            'roleId' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'roleId.required' => '角色id 必填',
            'roleId.integer'  => '角色id 必须为整型',
        ];
    }

    /**
     * 获取总数.
     * @param int $roleId 角色id
     * @param int $corpId 角色id
     */
    private function getEmployeesCountByRoleId(int $roleId, int $corpId): int
    {
        $where = [
            'roleId' => $roleId,
            'corpId' => $corpId,
        ];

        $employee = $this->workEmployeeService->getWorkEmployeesCountByRoleId($where);

        return ! empty($employee) ? $employee : 0;
    }
}
