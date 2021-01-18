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

interface WorkRoomServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkRoomList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkRoom(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkRooms(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkRoom(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkRooms(array $ids): int;

    /**
     * 计算客户群聊总数.
     * @param array $corpIds 企业id
     * @return int 返回值
     */
    public function countWorkRoomByCorpIds(array $corpIds): int;

    /**
     * 计算客户群聊总数.
     * @param array $corpIds 企业id
     * @param string $name 群聊名称
     * @return int 返回值
     */
    public function countWorkRoomByCorpIdsName(array $corpIds, $name): int;

    /**
     * 查询多条 根据名称.
     * @param array $corpId 企业ID
     * @param string $name 群名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomByCorpIdName(array $corpId, $name, array $columns = ['*']): array;

    /**
     * 修改多条 - 根据IDs.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomsById(array $ids, array $data): int;

    /**
     * 查询多条 根据名称.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsByCorpId(int $corpId, array $columns = ['*']): array;

    /**
     * 批量修改 - case...then...根据ID.
     * @param array $values 修改数据(必须包含ID)
     * @param bool $transToSnake 是否key转snake
     * @param bool $isColumnFilter 是否过滤不存在于表内的字段数据
     * @return int 影响条数
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int;

    /**
     * 修改多条 - 根据客户群聊分组.
     * @param int $roomGroupId 分组ID
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomsByRoomGroupId(int $roomGroupId, array $data): int;

    /**
     * 查询多条 根据微信群聊ID.
     * @param array $wxChatIds 微信群聊ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsByWxChatId(array $wxChatIds, array $columns = ['*']): array;

    /**
     * 查询多条 根据名称.
     * @param array $corpId 企业ID
     * @param array $params 名称、分组参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomByCorpIdNameGroupId(array $corpId, array $params = [], array $columns = ['*']): array;

    /**
     * 删除 - 单条
     * @param string $wxChatId 客户群微信ID
     * @return int 删除条数
     */
    public function deleteWorkRoomByWxChatId(string $wxChatId): int;

    /**
     * 计算某段时间内新增群聊数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countAddWorkRoomsByCorpIdTime(int $corpId, string $startTime, string $endTime): int;
}
