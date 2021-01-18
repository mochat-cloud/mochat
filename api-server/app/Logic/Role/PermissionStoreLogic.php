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

use App\Contract\RbacMenuServiceInterface;
use App\Contract\RbacRoleMenuServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 角色-权限保存.
 * @author
 *
 * Class PermissionStoreLogic
 */
class PermissionStoreLogic
{
    /**
     * 角色菜单关联表-角色权限.
     * @Inject
     * @var RbacRoleMenuServiceInterface
     */
    protected $roleMenuClient;

    /**
     * 菜单.
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuClient;

    /**
     * @param int $roleId 角色ID
     * @param array $menuIds 菜单IDs
     * @return bool 授权结果
     */
    public function handle(int $roleId, array $menuIds): bool
    {
        ## 所有菜单的所有上级菜单
        if (! empty($menuIds)) {
            $allMenus = $this->menuClient->getRbacMenusById($menuIds, ['id', 'path']);
            foreach ($allMenus as $allMenu) {
                $allMenuIds = null;
                preg_match_all('/#(\d+)#/', $allMenu['path'], $allMenuIds);
                isset($allMenuIds[1][0]) && $menuIds = array_merge($menuIds, $allMenuIds[1]);
            }
            $menuIds = array_unique($menuIds);
        }

        $dbData = $this->diffRoleMenu($roleId, $menuIds);
        if (empty($dbData)) {
            return true;
        }

        ## 模型操作
        Db::beginTransaction();
        try {
            empty($dbData['add']) || $this->roleMenuClient->createRbacRoleMenus($dbData['add']);
            empty($dbData['del']) || $this->roleMenuClient->deleteRbacRoleMenus($dbData['del']);

            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '角色赋权失败');
        }

        return true;
    }

    /**
     * 对比数据.
     * @param int $roleId ...
     * @param array $menuIds ...
     * @return array ...
     */
    protected function diffRoleMenu(int $roleId, array $menuIds): array
    {
        $res = [];
        ## 原菜单
        $oldMenuIds = $this->roleMenuClient->getRbacRoleMenusByRoleId([$roleId], ['id', 'menu_id']);
        $oldMenuIds = array_column($oldMenuIds, 'id', 'menuId');
        foreach ($menuIds as $menuId) {
            if (isset($oldMenuIds[$menuId])) {
                unset($oldMenuIds[$menuId]);
            } else {
                ## 添加
                $res['add'][] = [
                    'role_id' => $roleId,
                    'menu_id' => $menuId,
                ];
            }
        }
        ## 删除
        empty($oldMenuIds) || $res['del'] = array_values($oldMenuIds);

        return $res;
    }
}
