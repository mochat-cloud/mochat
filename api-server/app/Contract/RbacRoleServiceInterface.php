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

interface RbacRoleServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacRoleById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacRolesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRbacRoleList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRbacRole(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRbacRoles(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRbacRoleById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRbacRole(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRbacRoles(array $ids): int;

    /**
     * 查询多条 - 根据租户ID.
     * @param int $tenantId 租户ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacRolesByTenantId(int $tenantId, array $columns = ['*']): array;

    /**
     * 根据角色id和租户id获取角色详情.
     * @param int $id 角色id
     * @param int $tenantId 租户id
     * @param array $columns 字段
     */
    public function getRbacRolesByIdTenantId(int $id, int $tenantId, array $columns = ['*']): array;

    /**
     * 根据角色名称和租户id获取角色详情.
     * @param string $name 角色id
     * @param int $tenantId 租户id
     * @param array $columns 字段
     */
    public function getRoleByNameTenantId(string $name, int $tenantId, array $columns = ['*']): array;
}
