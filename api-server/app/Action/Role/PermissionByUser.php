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

use App\Action\Corp\Traits\RequestTrait;
use App\Constants\Menu\IsPageMenu;
use App\Contract\RbacMenuServiceInterface;
use App\Contract\RbacRoleMenuServiceInterface;
use App\Contract\RbacRoleServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 权限 - 根据登录用户获取菜单权限.
 *
 * Class PermissionByUser.
 * @Controller
 */
class PermissionByUser extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    private $roleService;

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
     * @RequestMapping(path="/role/permissionByUser", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 用户信息
        $user = user();

        ## 账户是超级管理员-获取全部菜单
        $columns = ['id', 'name', 'level', 'data_permission', 'icon', 'link_type', 'link_url', 'parent_id', 'is_page_menu'];
        if (! empty($user['isSuperAdmin']) && $user['isSuperAdmin'] == 1) {
            $menus = $this->menuService->getMenusByIsPageMenu(IsPageMenu::YES, $columns);
            return $this->recursion($menus);
        }

        ## 根据角色id获取菜单权限
        $roleId   = (int) $user['roleId'];
        $roleMenu = $this->roleMenuService->getRbacRoleMenusByRoleId([$roleId], ['menu_id']);
        if (empty($roleMenu)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该角色未设置菜单权限');
        }
        $menuIds = array_column($roleMenu, 'menuId');

        ## 根据菜单id获取菜单列表
        $menus = $this->menuService->getRbacMenusById($menuIds, $columns);
        if (empty($menus)) {
            return [];
        }

        $menus = $this->filterData($menus);
        return $this->recursion($menus);
    }

    /**
     * 无限递归数据.
     * @param array $data 数据
     * @param int $id 主键id
     */
    private function recursion(array $data, int $id = 0): array
    {
        $tree = [];
        foreach ($data as $key => $val) {
            if ($val['parentId'] != $id) {
                continue;
            }
            $val['menuId'] = $val['id'];

            unset($data[$key]);
            $val['children'] = $this->recursion($data, $val['id']);
            $tree[]          = $val;
        }

        return $tree;
    }

    /**
     * 获取出来页面菜单.
     * @param array $menus 菜单数据
     * @return array 数据
     */
    private function filterData(array $menus): array
    {
        $data = [];
        foreach ($menus as $v) {
            if ($v['isPageMenu'] != IsPageMenu::YES) {
                continue;
            }
            $data[] = $v;
        }
        return $data;
    }
}
