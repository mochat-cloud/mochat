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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 租户下-角色下拉列表.
 *
 * Class Select.
 * @Controller
 */
class Select extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    private $roleService;

    /**
     * @RequestMapping(path="/role/select", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user     = user();
        $tenantId = $user['tenantId'];

        ## 根据租户id获取角色列表
        $roles = $this->roleService->getRbacRolesByTenantId($tenantId, ['id', 'name']);
        if (empty($roles)) {
            return [];
        }

        ## 组织响应数据
        $data = [];
        foreach ($roles as $v) {
            $data[] = [
                'roleId' => $v['id'],
                'name'   => $v['name'],
            ];
        }

        return $data;
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
}
