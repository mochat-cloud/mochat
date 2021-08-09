<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Contract;

interface RoomCalendarPushContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomCalendarPushById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomCalendarPushsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRoomCalendarPushList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomCalendarPush(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomCalendarPushs(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomCalendarPushById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomCalendarPush(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomCalendarPushs(array $ids): int;

    /**
     * 查询.
     */
    public function getRoomCalendarPushByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * 删除 - 多条
     * @param int $roomCalendarId 群日历id
     * @return int 删除条数
     */
    public function deleteRoomCalendarPushByByRoomCalendarId(int $roomCalendarId): int;

    /**
     * 查询多条-roomCalendarId.
     * @param array|string[] $columns 查询字段
     */
    public function getRoomCalendarPushByRoomCalendarId(int $roomCalendarId, array $columns = ['*']): array;

    /**
     * 更新 - 多条
     * @param int $roomCalendarId 群日历id
     * @param array $data 修改数据
     * @return int 删除条数
     */
    public function updateRoomCalendarPushByByRoomCalendarId(int $roomCalendarId, array $data): int;

    /**
     * 查询多条-day.
     * @param array|string[] $columns
     */
    public function getRoomCalendarPushByDay(string $day, array $columns = ['*']): array;

    /**
     * 查询多条-roomCalendarIdDay.
     * @param array|string[] $columns 查询字段
     */
    public function getRoomCalendarPushByRoomCalendarIdDay(int $roomCalendarId, string $day, array $columns = ['*']): array;
}
