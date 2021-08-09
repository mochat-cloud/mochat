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

interface ClockInContactContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getClockInContactList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createClockInContact(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createClockInContacts(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContactById(int $id, array $data): int;

    /**
     * 修改多条 - 根据IDs.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContact(array $ids, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContact(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContacts(array $ids): int;

    /**
     * 查询.
     */
    public function getClockInContactByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * 查询总打卡天数-活动id.
     */
    public function sumClockInContactTotalDayByClockInId(int $ClockInId): int;

    /**
     * 查询总打卡人数-活动id.
     */
    public function countClockInContactByClockInId(int $ClockInId): int;

    /**
     * 查询单条-clockInId&unionId.
     * @param array|string[] $columns
     */
    public function getClockInContactByClockInIdUnionId(int $clockInId, string $unionId, array $columns = ['*']): array;

    /**
     * 查询多条-groupSeriesDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupSeriesDay(int $clockInId, array $columns = ['*']): array;

    /**
     * 查询多条-groupTotalDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupTotalDay(int $clockInId, array $columns = ['*']): array;

    /**
     * 查询10条-groupSeriesDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupSeriesDayLimit(int $clockInId, array $columns = ['*']): array;

    /**
     * 查询10条-groupTotalDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupTotalDayLimit(int $clockInId, array $columns = ['*']): array;

    /**
     * @param array|string[] $columns
     */
    public function getClockInContactCityById(int $clockInId, array $columns = ['*']): array;
}
