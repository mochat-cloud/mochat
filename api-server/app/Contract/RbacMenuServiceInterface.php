<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Contract;

interface RbacMenuServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacMenuById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacMenusById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRbacMenuList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRbacMenu(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRbacMenus(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRbacMenuById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRbacMenu(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRbacMenus(array $ids): int;

    /**
     * 根据条件查询多条.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMenusBySearch(array $where, array $columns = ['*']): array;

    /**
     * 根据路径模糊查询多条.
     * @param string $path 菜单路径
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getMenusByPath(string $path, array $columns = ['*']): array;

    /**
     * 修改多条 - 根据ID.
     * @param array $ids 多条id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRbacMenuByIds(array $ids, array $data): int;

    /**
     * 获取某状态下菜单.
     *
     * @param int $status 状态值
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getMenusByStatus(int $status = 1, array $columns = ['*']): array;

    /**
     * 根据菜单名称模糊搜索.
     * @param string $name 菜单名称
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByName(string $name, array $columns = ['*']): array;

    /**
     * 根据路径模糊搜索菜单.
     * @param array $path path 路径id
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByPaths(array $path, array $columns = ['*']): array;

    /**
     * 获取单条 - 根据链接.
     * @param string $linkUrl ...
     * @param array|string[] $columns ...
     * @return array ...
     */
    public function getMenuByLinkUrl(string $linkUrl, array $columns = ['*']): array;

    /**
     * 根据用户获取所有页面菜单.
     *
     * @param int $isPageMenu 字段
     * @param array $columns 字段
     * @return array 数组
     */
    public function getMenusByIsPageMenu(int $isPageMenu, array $columns = ['*']): array;
}
