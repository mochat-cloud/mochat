<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Menu;

use App\Constants\Menu\Level;
use App\Contract\RbacMenuServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 菜单-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * 菜单.
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($params);

        ## 查询数据
        return $this->getMenu($params);
    }

    /**
     * 处理参数.
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $this->perPage = $params['perPage'];
        $this->page    = $params['page'];
        ## 列表查询条件
        $where = [];
        if (! empty($params['name'])) {
            //$where['name'] = $params['name'];
            $menuIds     = $this->getMenuIdsByName($params['name']);
            $where['id'] = $menuIds;
        }

        return $where;
    }

    /**
     * 通过菜单名称模糊获取菜单id.
     *
     * @param string $name 菜单名称
     * @return array 菜单id
     */
    private function getMenuIdsByName(string $name): array
    {
        ## 模糊搜索菜单结果
        $menus = $this->menuService->getMenusByName($name, ['id', 'path']);
        ## 处理path
        if (empty($menus)) {
            return [];
        }

        ## 获取每一级别的顶级部门
        $menuIds = [];
        foreach ($menus as $k => $v) {
            if (strpos($v['path'], '-')) {
                $menuIds[] = str_replace('#', '', explode('-', $v['path'])[0]);
            } else {
                $menuIds[] = str_replace('#', '', $v['path']);
            }
        }

        ## 父级部门以下的子部门id
        $paths       = array_unique($menuIds);
        $childrenIds = $this->menuService->getMenusByPaths($paths, ['id']);
        if (empty($childrenIds)) {
            return [];
        }

        return array_column($childrenIds, 'id');
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getMenu(array $params): array
    {
        ## 分页查询数据表
        $columns = ['id', 'name', 'level', 'parent_id', 'icon', 'status', 'operate_name', 'updated_at'];

        if (isset($params['id']) && empty($params['id'])) {
            return [
                'page' => [
                    'perPage'   => $this->perPage,
                    'total'     => '0',
                    'totalPage' => '0',
                ],
                'list' => [],
            ];
        }
        $menu = $this->menuService->getMenusBySearch($params, $columns);

        ## 返回数据
        $data = [
            'page' => [
                'perPage'   => $this->perPage,
                'total'     => '0',
                'totalPage' => '0',
            ],
            'list' => [],
        ];

        return empty($menu) ? $data : $this->handleData($data, $menu);
    }

    /**
     * @param array $data 请求参数
     * @param array $menu 菜单列表
     * @return array 响应数组
     */
    private function handleData(array $data, array $menu): array
    {
        ## 无限递归处理数据
        $tree = $this->recursion($menu);
        //array_multisort($tree, SORT_DESC);

        $total     = count($tree);
        $totalPage = ceil($total / $this->perPage);
        $offset    = ($this->page - 1) * $this->perPage;

        ## 分页数据
        $data['page']['total']     = $total;
        $data['page']['totalPage'] = $totalPage;
        $data['list']              = array_slice($tree, $offset, $this->perPage);

        return $data;
    }

    /**
     * 无限递归数据.
     * @param array $data 数据
     * @param int $id 主键id
     * @param string $path 路径
     * @return array
     */
    private function recursion(array $data, int $id = 0, string $path = '')
    {
        $tree    = [];
        $pathKey = 1;
        foreach ($data as $key => $val) {
            if ($val['parentId'] != $id) {
                continue;
            }

            $val['menuId']   = $val['id'];
            $val['menuPath'] = $val['parentId'] ? $path . '-' . $pathKey : $pathKey;
            ++$pathKey;
            $val['menuPath']  = (string) $val['menuPath'];
            $val['levelName'] = ! empty($val['level']) ? Level::getMessage($val['level']) : '';

            unset($data[$key]);
            $val['children'] = $this->recursion($data, $val['id'], $val['menuPath']);
            $tree[]          = $val;
        }

        return $tree;
    }
}
