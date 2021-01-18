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
use Hyperf\Di\Annotation\Inject;

/**
 * 角色-权限查看.
 *
 * @author
 *
 * Class PermissionShowLogic
 */
class PermissionShowLogic
{
    /**
     * @Inject
     *
     * @var RbacRoleMenuServiceInterface
     */
    protected $roleMenuClient;

    /**
     * @Inject
     *
     * @var RbacMenuServiceInterface
     */
    protected $menuClient;

    public function roleMenus(int $roleId): array
    {
        ## 角色与菜单的关联
        $roleMenus      = $this->roleMenuClient->getRbacRoleMenusByRoleId([$roleId], ['id', 'menu_id']);
        $checkedMenuIds = array_flip(array_column($roleMenus, 'menuId'));

        ## 所有菜单
        $menus = $this->menuClient->getMenusByStatus(1, ['id', 'parent_id', 'name', 'level', 'is_page_menu']);

        return $this->recu($menus, 0, $checkedMenuIds);
    }

    /**
     * 递归菜单.
     * @param array $data ...
     * @param int $id ...
     * @param array $checkedMenuIds 选中权限ID
     * @return array ...
     */
    protected function recu(array $data, int $id = 0, array $checkedMenuIds): array
    {
        $newData = [];

        foreach ($data as $key => $val) {
            if ($val['parentId'] !== $id) {
                continue;
            }
            // (默认)未选
            $val['checked'] = 1;

            $newData[$val['id']] = $val;
            unset($data[$key]);
            $newData[$val['id']]['children'] = $this->recu($data, $val['id'], $checkedMenuIds);

            // (无子级)全选
            if (empty($newData[$val['id']]['children'])) {
                isset($checkedMenuIds[$val['id']]) && $newData[$val['id']]['checked'] = 2;
                continue;
            }

            // 子级选中结果['子级个数', '未选个数', '全选个数', '半选个数']
            $checkRes = array_reduce($newData[$val['id']]['children'], function ($carry, $item) {
                ++$carry[0];
                ++$carry[$item['checked']];
                return $carry;
            }, [0, 0, 0, 0]);
            // (有子级)全选
            if ($checkRes[0] === $checkRes[2]) {
                $newData[$val['id']]['checked'] = 2;
                continue;
            }
            // (有子级)半选
            if ($checkRes[0] !== $checkRes[1]) {
                $newData[$val['id']]['checked'] = 3;
            }
        }

        return array_values($newData);
    }
}
