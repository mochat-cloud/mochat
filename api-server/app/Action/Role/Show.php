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
 * 角色详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    private $roleService;

    /**
     * @var int 企业id
     */
    private $corpId;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/show", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user         = user();
        $tenantId     = $user['tenantId'];
        $this->corpId = $user['corpIds'][0];

        ## 验证参数 接受参数
        $this->validated($this->request->all());
        $roleId = (int) $this->request->input('roleId');

        ## 根据租户id获取角色列表
        $role = $this->roleService->getRbacRolesByIdTenantId($roleId, $tenantId, ['id', 'name', 'remarks', 'data_permission']);
        if (empty($role)) {
            throw new CommonException(ErrorCode::URI_NOT_FOUND, '角色不存在');
        }

        ## 组织响应数据
        $dataPermission = empty($role['dataPermission']) ? '' : $this->handleDataPermission(json_decode($role['dataPermission'], true));
        return [
            'roleId'         => $role['id'],
            'name'           => $role['name'],
            'remarks'        => $role['remarks'],
            'dataPermission' => (int) $dataPermission,
        ];
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
            'roleId.required' => '角色ID 必填',
            'roleId.integer'  => '角色ID 必需为整数',
        ];
    }

    /**
     * 获取当前企业的数据权限.
     * @param array $data 数据
     * @return array
     */
    private function handleDataPermission(array $data): int
    {
        $dataPermission = '';
        foreach ($data as $k => $v) {
            if ($v['corpId'] == $this->corpId) {
                $dataPermission = $v['permissionType'];
            }
        }
        return (int) $dataPermission;
    }
}
