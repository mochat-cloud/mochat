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

interface UserServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getUserById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getUsersById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getUserList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createUser(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createUsers(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateUserById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteUser(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteUsers(array $ids): int;

    /**
     * 查询单条 - 根据手机号.
     * @param string $phone 手机号
     * @param array $columns 查询字段
     * @return null|\App\Model\User|\Hyperf\Database\Model\Builder|object
     */
    public function getUserByPhone(string $phone, array $columns = ['*']);

    /**
     * 查询单条模型 - ID.
     * @param int $id ...
     * @param array $columns 查询字段
     * @return null|\App\Model\User|\Hyperf\Database\Model\Builder|object
     */
    public function getUserModelById(int $id, array $columns = ['*']);

    /**
     * 修改账户状态 - 根据ID.
     * @param array $ids id
     * @param int $status 账户状态
     * @return int 修改条数
     */
    public function updateUserStatusById(array $ids, int $status): int;

    /**
     * 修改账户密码 - 根据ID.
     * @param int $id id
     * @param string $password 密码
     * @return int 修改条数
     */
    public function updateUserPasswordById(int $id, string $password): int;

    /**
     * 查询多条 - 根据手机号.
     * @param array $phones 手机号
     * @param array $columns 查询字段
     * @return array 响应数组
     */
    public function getUsersByPhone(array $phones, array $columns = ['*']): array;

    /**
     * 查询多条.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getUsers(array $columns = ['*']): array;

    /**
     * 查询多条 - 根据租户ID.
     * @param int $tenantId 手机号
     * @param array $columns 查询字段
     * @return array 响应数组
     */
    public function getUsersByTenantId(int $tenantId, array $columns = ['*']): array;
}
