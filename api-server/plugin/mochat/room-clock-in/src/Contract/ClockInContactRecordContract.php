<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Contract;

interface ClockInContactRecordContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactRecordById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactRecordsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getClockInContactRecordList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createClockInContactRecord(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createClockInContactRecords(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContactRecordById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContactRecord(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContactRecords(array $ids): int;

    /**
     * 查询.
     */
    public function getClockInContactRecordByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * 查询总打卡天数-活动id.
     */
    public function sumClockInContactRecordTotalDayByClockInId(int $ClockInId): int;

    /**
     * 查询总打卡人数-活动id.
     */
    public function countClockInContactRecordByClockInId(int $ClockInId): int;

    /**
     * 查询今日打卡人数-活动id.
     */
    public function countInContactRecordTodayByClockInId(int $clockInId): int;

    /**
     * 查询单条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array;

    /**
     * 查询多条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordsByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array;

    /**
     * 查询单条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordLastByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array;
}
