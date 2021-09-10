<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Rbac\Action\Dashboard\Role;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Action\Dashboard\Traits\RequestTrait;
use MoChat\App\Rbac\Constants\Menu\IsPageMenu;
use MoChat\App\Rbac\Contract\RbacMenuContract;
use MoChat\App\Rbac\Contract\RbacRoleContract;
use MoChat\App\Rbac\Contract\RbacRoleMenuContract;
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
     * @var RbacMenuContract
     */
    protected $menuService;

    /**
     * @Inject
     * @var RbacRoleContract
     */
    private $roleService;

    /**
     * 角色菜单关联表-角色权限.
     * @Inject
     * @var RbacRoleMenuContract
     */
    private $roleMenuService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/role/permissionByUser", methods="get")
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
            $this->replaceLinkUrl($menus);
            return $this->recursion($menus);
        }

        ## 根据角色id获取菜单权限
        $roleId = (int) $user['roleId'];
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

        $this->replaceLinkUrl($menus);
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
            $tree[] = $val;
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

    /**
     * 替换链接.
     */
    private function replaceLinkUrl(array &$menus)
    {
        foreach ($menus as $index => $menu) {
            if (isset($menu['linkUrl'])) {
                $menus[$index]['linkUrl'] = str_replace('/dashboard', '', $menu['linkUrl']);
            }
        }
    }
}
