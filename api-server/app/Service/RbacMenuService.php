<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Service;

use App\Contract\RbacMenuServiceInterface;
use App\Model\RbacMenu;
use MoChat\Framework\Service\AbstractService;

class RbacMenuService extends AbstractService implements RbacMenuServiceInterface
{
    /**
     * @var RbacMenu
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     *
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getRbacMenuById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     *
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getRbacMenusById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 多条分页.
     *
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     *
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRbacMenuList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     *
     * @param array $data 添加的数据
     *
     * @return int 自增ID
     */
    public function createRbacMenu(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     *
     * @param array $data 添加的数据
     *
     * @return bool 执行结果
     */
    public function createRbacMenus(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     *
     * @param int $id id
     * @param array $data 修改数据
     *
     * @return int 修改条数
     */
    public function updateRbacMenuById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     *
     * @param int $id 删除ID
     *
     * @return int 删除条数
     */
    public function deleteRbacMenu(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     *
     * @param array $ids 删除ID
     *
     * @return int 删除条数
     */
    public function deleteRbacMenus(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 根据条件查询多条.
     *
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getMenusBySearch(array $where, array $columns = ['*']): array
    {
        $res = $this->model::query();

        if (! empty($where['name'])) {
            $res = $res->where('name', 'like', "%{$where['name']}%");
        }

        if (! empty($where['id']) && is_array($where['id'])) {
            $res = $res->whereIn('id', $where['id']);
        }

        $res = $res->orderBy('id', 'asc');
        $res = $res->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 根据路径模糊查询多条.
     *
     * @param string $path 菜单路径
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getMenusByPath(string $path, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('path', 'like', "%{$path}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 修改多条 - 根据ID.
     *
     * @param array $ids 多条id
     * @param array $data 修改数据
     *
     * @return int 修改条数
     */
    public function updateRbacMenuByIds(array $ids, array $data): int
    {
        return $this->model::query()
            ->whereIn('id', $ids)
            ->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenusByStatus(int $status = 1, array $columns = ['*']): array
    {
        $data          = $this->model::query()->where('status', $status)->get($columns);
        $data || $data = collect([]);

        return $data->toArray();
    }

    /**
     * 根据菜单名称模糊搜索.
     * @param string $name 菜单名称
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByName(string $name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('name', 'like', "%{$name}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 根据路径模糊搜索菜单.
     *
     * @param array $paths path 路径id
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByPaths(array $paths, array $columns = ['*']): array
    {
        $res = $this->model::query();

        if (! empty($paths) && is_array($paths)) {
            foreach ($paths as $k => $v) {
                if ($k == 0) {
                    $res = $res->where('path', 'like', '%#' . $v . '#%');
                } else {
                    $res = $res->Orwhere('path', 'like', '%#' . $v . '#%');
                }
            }
        }

        $res = $res->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuByLinkUrl(string $linkUrl, array $columns = ['*']): array
    {
        $data          = $this->model::query()->where('link_url', $linkUrl)->first($columns);
        $data || $data = collect([]);

        return $data->toArray();
    }

    /**
     * 根据用户获取所有页面菜单.
     *
     * @param int $isPageMenu 字段
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByIsPageMenu(int $isPageMenu, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('is_page_menu', $isPageMenu)
            ->orderBy('sort')
            ->get($columns);

        return empty($data) ? [] : $data->toArray();
    }
}
