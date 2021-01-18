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

interface WorkContactRoomServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactRoomList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactRoom(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactRooms(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactRoomById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactRoom(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactRooms(array $ids): int;

    /**
     * 查询多条 - 根据客户群ID.
     * @param int $roomId 客户群ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsByRoomId(int $roomId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据客户id.
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsByContactId($contactId, array $columns = ['*']): array;

    /**
     * @param array $roomIds 群聊ID数组
     * @param int $status 成员状态
     * @return array 响应数组
     */
    public function countWorkContactRoomsByRoomIds(array $roomIds, int $status = 0): array;

    /**
     * 多条分页.- 群成员分页搜索.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param int $perPage 每页条数
     * @param int $page 页码
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactRoomIndex(array $where, array $columns = ['*'], int $perPage, int $page): array;

    /**
     * 查询多条 - 根据客户id.
     * @param int $employeeId 通讯录ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsByEmployeeId(int $employeeId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据客户群ID.
     * @param array $roomIds 客户群ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsByRoomIds(array $roomIds, array $columns = ['*']): array;

    /**
     * 查询客户持群数.
     */
    public function getWorkContactRoomNum(): array;

    /**
     * 查询多条 - 根据客户id和类型.
     * @param array $contactIds 客户id
     * @param int $type 类型
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactRoomsByContactIdsType(array $contactIds, int $type, array $columns = ['*']): array;

    /**
     * 批量修改 - case...then...根据ID.
     * @param array $values 修改数据(必须包含ID)
     * @param bool $transToSnake 是否key转snake
     * @param bool $isColumnFilter 是否过滤不存在于表内的字段数据
     * @return int 影响条数
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int;

    /**
     * 修改多条 - 根据ID数组.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactRoomByIds(array $ids, array $data): int;

    /**
     * 查询多条 - 根据微信用户id.
     * @param string $wxUserId 通讯录ID
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactRoomsByWxUserId($wxUserId, array $columns = ['*']): array;

    /**
     * 计算某段时间内新增入群数.
     * @param array $roomId 群id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countAddWorkContactRoomsByRoomIdTime(array $roomId, string $startTime, string $endTime): int;

    /**
     * 计算某段时间内退群数.
     * @param array $roomId 群id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countQuitWorkContactRoomsByRoomIdTime(array $roomId, string $startTime, string $endTime): int;

    /**
     * 计算企业总群人数.
     * @param array $roomId 群id
     * @return int 返回值
     */
    public function countWorkContactRoomByRoomIds(array $roomId): int;
}
