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

interface ContactMessageBatchSendResultContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactMessageBatchSendResultById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactMessageBatchSendResultsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getContactMessageBatchSendResultList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactMessageBatchSendResult(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactMessageBatchSendResults(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactMessageBatchSendResultById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactMessageBatchSendResult(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactMessageBatchSendResults(array $ids): int;

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getContactMessageBatchSendResultsBySearch(array $params): array;

    /**
     * 根据群发ID删除 - 多条
     * @param int $batchId 群发ID
     */
    public function deleteContactMessageBatchSendResultsByBatchId(int $batchId): int;

    /**
     * 获取微信userId.
     * @param int $batchId 群发ID
     * @param int $employeeId 成员ID
     */
    public function getExternalUserIdsByBatchEmployeeId(int $batchId, int $employeeId): array;

    /**
     * 根据群发Id微信Id获取单条记录.
     * @param array|string[] $columns
     */
    public function getContactMessageBatchSendResultByBatchUserId(int $batchId, string $externalUserUd, array $columns = ['*']): array;

    /**
     * 获取结果统计
     */
    public function getContactMessageBatchSendResultCount(array $where): int;
}
