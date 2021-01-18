<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Tool\Rbac;

use App\Contract\RbacMenuServiceInterface;
use App\Contract\RbacRoleMenuServiceInterface;
use App\Contract\RbacRoleServiceInterface;
use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\UserServiceInterface;
use App\Logic\Menu\StoreLogic as MenuStoreLogic;
use App\Logic\Role\PermissionStoreLogic;
use Hyperf\Di\Annotation\Inject;

class Rbac
{
    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuClient;

    /**
     * @Inject
     * @var RbacRoleMenuServiceInterface
     */
    protected $roleMenuClient;

    /**
     * @Inject
     * @var RbacUserRoleServiceInterface
     */
    protected $userRoleClient;

    /**
     * @Inject
     * @var PermissionStoreLogic
     */
    protected $userRoleLogic;

    /**
     * @Inject
     * @var UserServiceInterface
     */
    protected $userClient;

    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $roleClient;

    /**
     * @Inject
     * @var MenuStoreLogic
     */
    protected $menuLogic;

    /**
     * 用户是否有某权限.
     * @param int $userId 子账户ID
     * @param int|string $permission uriPath#method[如:/user#get]|或权限id
     * @return bool 结果
     */
    public function userCan(int $userId, $permission): bool
    {
        ## 权限ID
        if (is_string($permission)) {
            $menu = $this->menuClient->getMenuByLinkUrl($permission, ['id']);
            if (empty($menu)) {
                return false;
            }
            $menuId = $menu['id'];
        } else {
            $menuId = $permission;
        }

        ## 用户下角色
        $userRoles = $this->userRoles($userId, ['id']);
        if (empty($userRoles)) {
            return false;
        }
        $roleIds = array_column($userRoles, 'id');

        ## 角色下权限
        $menus = $this->roleMenuClient->getRbacRoleMenusByRoleId($roleIds, ['id', 'menu_id']);
        $menus = array_column($menus, 'id', 'menuId');

        return isset($menus[$menuId]);
    }

    /**
     * 用户下所有角色.
     * @param int $userId ...
     * @param array|string[] $roleColumns ...
     * @return array ...
     */
    public function userRoles(int $userId, array $roleColumns = ['*']): array
    {
        ## 所有角色id
        $roleIds = $this->userRoleClient->getRbacUserRolesByUserId([$userId], ['id', 'role_id']);
        if (empty($roleIds)) {
            return [];
        }

        $roleIds = array_column($roleIds, 'roleId');

        ## 用户租户
        $user     = $this->userClient->getUserById($userId, ['id', 'tenant_id']);
        $tenantId = (int) $user['tenantId'];

        ## 所有角色
        in_array('tenant_id', $roleColumns, true) || $roleColumns[] = 'tenant_id';
        $roles                                                      = $this->roleClient->getRbacRolesById($roleIds, $roleColumns);

        ## 过滤角色
        return array_reduce($roles, function ($carry, $item) use ($tenantId) {
            (int) $item['tenantId'] === $tenantId && $carry[] = $item;
            return $carry;
        }, []);
    }

    /**
     * 用户下所有权限.
     * @param int $userId ...
     * @param int|string $permission uriPath#method[如:/user#get]|或权限id
     * @param int $corpId 企业ID
     * @return array ...
     */
    public function userPermissions(int $userId, $permission = 0, int $corpId = 0): array
    {
        ## 角色
        $roles = $this->userRoles($userId, ['id', 'name', 'data_permission', 'status']);
        $roles = array_reduce($roles, function ($carry, $item) {
            $carry[$item['id']] = $item;
            return $carry;
        }, []);

        $roleMenusData = $this->roleMenuClient->getRbacRoleMenusByRoleId(array_column($roles, 'id'), ['id', 'role_id', 'menu_id']);

        ## 权限
        $menus = $this->menuClient->getRbacMenusById(array_column($roleMenusData, 'menuId'), [
            'id', 'name', 'status', 'link_type', 'link_url', 'data_permission',
        ]);

        $roleMenus = array_column($roleMenusData, 'roleId', 'menuId');
        foreach ($menus as &$menu) {
            $menu['roleId'] = $roleMenus[$menu['id']];
            if ($menu['dataPermission'] !== 2) {
                $permissionTypeArr = json_decode($roles[$roleMenus[$menu['id']]]['dataPermission'], true);
                if (isset($permissionTypeArr[0])) {
                    $permissionTypeData                                            = array_column($permissionTypeArr, 'permissionType', 'corpId');
                    isset($permissionTypeData[$corpId]) && $menu['dataPermission'] = $permissionTypeData[$corpId];
                }
            } else {
                $menu['dataPermission'] = 0;
            }

            if (! $permission) {
                continue;
            }
            if (is_string($permission) && $permission === $menu['linkUrl']) {
                return $menu;
            }
            if (is_int($permission) && $permission === $menu['id']) {
                return $menu;
            }
        }
        return $menus;
    }

    /**
     * 添加权限.
     * @param array $data 菜单数据 必须参数为[['name' => 'xxx', 'link_type' => 1, 'link_url' => '/path#get', 'is_page_menu' => 1]]
     * @param string $parentLinkUrl 父菜单的链接地址
     * @return bool ...
     */
    public function permissionsCreate(array $data, string $parentLinkUrl = ''): bool
    {
        return $this->menuLogic->createMenus($data, $parentLinkUrl);
    }

    /**
     * 给角色添加权限.
     * @param int $roleId 角色ID
     * @param array $menuIds 菜单IDs
     * @return bool 授权结果
     */
    public function permissionsToRole(int $roleId, array $menuIds): bool
    {
        return $this->userRoleLogic->handle($roleId, $menuIds);
    }
}
