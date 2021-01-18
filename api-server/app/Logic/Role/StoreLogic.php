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

use App\Contract\RbacRoleMenuServiceInterface;
use App\Contract\RbacRoleServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 角色-添加.
 *
 * Class IndexLogic
 */
class StoreLogic
{
    /**
     * 角色.
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $roleService;

    /**
     * 角色菜单关联表-角色权限.
     * @Inject
     * @var RbacRoleMenuServiceInterface
     */
    private $roleMenuService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var int 租户id
     */
    private $tenantId;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        $this->tenantId = $user['tenantId'];
        ## 处理参数
        $data = $this->handleParam($user, $params);

        if (! empty($params['roleId'])) {
            ## 复制权限添加角色
            return $this->createRoleMenu($data, (int) $params['roleId']);
        }
        ## 创建角色
        return $this->createRole($data);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return bool
     */
    private function handleParam(array $user, array $params): array
    {
        return [
            'name'            => $params['name'],
            'tenant_id'       => $user['tenantId'],
            'remarks'         => $params['remarks'],
            'operate_id'      => $user['id'],
            'operate_name'    => $user['name'],
            'data_permission' => json_encode([['corpId' => $user['corpIds'][0], 'permissionType' => $params['dataPermission']]]),
            'created_at'      => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建角色.
     * @param array $data 数据
     */
    private function createRole(array $data): array
    {
        ## 验证名称是否存在
        $this->nameIsUnique($data['name']);

        ## 创建角色
        $roleRes = $this->roleService->createRbacRole($data);

        if (! $roleRes) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '角色创建失败');
        }
        return [];
    }

    /**
     * 创建角色并赋予权限.
     *
     * @param array $data 角色数据
     * @param int $roleId 接受的角色ID
     */
    private function createRoleMenu(array $data, int $roleId): array
    {
        ## 验证名称是否存在
        $this->nameIsUnique($data['name']);
        ## 根据角色获取菜单权限
        $roleMenus = $this->roleMenuService->getRbacRoleMenusByRoleId([$roleId], ['menu_id']);
        if (! $roleMenus) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '该角色还没有设置权限');
        }

        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建角色
            $newRoleId = $this->roleService->createRbacRole($data);

            ## 角色权限数据
            $roleMenuData = [];
            foreach ($roleMenus as $key => &$val) {
                $roleMenuData[$key] = [
                    'role_id'    => $newRoleId,
                    'menu_id'    => $val['menuId'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }

            ## 添加角色权限
            $this->roleMenuService->createRbacRoleMenus($roleMenuData);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '复制权限创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '复制权限创建失败');
        }

        return [];
    }

    /**
     * 验证角色名称是否存在.
     *
     * @param string $name 角色名称
     * @return bool
     */
    private function nameIsUnique(string $name)
    {
        $existData = $this->roleService->getRoleByNameTenantId($name, $this->tenantId);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $name . '-该角色已存在');
        }
        return true;
    }
}
