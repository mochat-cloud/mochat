<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Contract;

interface RoomMessageBatchSendResultContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomMessageBatchSendResultById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomMessageBatchSendResultsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRoomMessageBatchSendResultList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomMessageBatchSendResult(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomMessageBatchSendResults(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomMessageBatchSendResultById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomMessageBatchSendResult(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomMessageBatchSendResults(array $ids): int;

    /**
     * 根据群发ID获取群ID数组.
     * @param int $batchId 群发ID
     */
    public function getRoomMessageBatchSendResultRoomIdsByBatchIds(int $batchId): array;

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getRoomMessageBatchSendResultsBySearch(array $params): array;

    /**
     * 根据群发ID删除 - 多条
     * @param int $batchId 群发ID
     */
    public function deleteRoomMessageBatchSendResultsByBatchId(int $batchId): int;

    /**
     * 获取微信userId.
     * @param int $batchId 群发ID
     * @param int $employeeId 成员ID
     */
    public function getExternalUserIdsByBatchEmployeeId(int $batchId, int $employeeId): array;

    /**
     * 根据客户群id获取结果列表.
     * @param array|string[] $columns
     */
    public function getRoomMessageBatchSendResultByBatchRoomId(int $batchId, string $chatId, array $columns = ['*']): array;

    /**
     * 获取结果统计
     */
    public function getRoomMessageBatchSendResultCount(array $where): int;
}
