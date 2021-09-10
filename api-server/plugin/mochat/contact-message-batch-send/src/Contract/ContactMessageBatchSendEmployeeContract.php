<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Contract;

interface ContactMessageBatchSendEmployeeContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactMessageBatchSendEmployeeById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactMessageBatchSendEmployeesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getContactMessageBatchSendEmployeeList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactMessageBatchSendEmployee(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactMessageBatchSendEmployees(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactMessageBatchSendEmployeeById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactMessageBatchSendEmployee(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactMessageBatchSendEmployees(array $ids): int;

    /**
     * join检索 - 多条
     */
    public function getContactMessageBatchSendEmployeesBySearch(array $params): array;

    /**
     * 根据群发ID获取多条
     * @param int $batchId 群发Id
     * @param array $ids 群发成员Id
     * @param array|string[] $columns
     */
    public function getContactMessageBatchSendEmployeesByBatchId(int $batchId, array $ids = [], array $columns = ['*']): array;

    /**
     * 获取需要同步结果的成员记录.
     */
    public function getContactMessageBatchSendEmployeesByResultSync(int $minutes, int $expireMinutes, int $limit = 1000): array;

    /**
     * 根据群发ID删除多条
     * @param int $batchId 群发ID
     */
    public function deleteContactMessageBatchSendEmployeesByBatchId(int $batchId): int;

    /**
     * 加锁查询单条 - 根据ID.
     * @param array|string[] $columns
     */
    public function getContactMessageBatchSendEmployeeLockById(int $id, array $columns = ['*']): array;

    /**
     * 获取成员统计
     */
    public function getContactMessageBatchSendEmployeeCount(array $where): int;

    /**
     * 查询最近一周的群发消息.
     */
    public function getContactMessageBatchSendEmployeeIdsByLastWeek(): array;
}
