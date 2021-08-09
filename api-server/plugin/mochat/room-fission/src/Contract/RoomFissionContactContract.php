<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Contract;

interface RoomFissionContactContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomFissionContactById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomFissionContactsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRoomFissionContactList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomFissionContact(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomFissionContacts(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomFissionContactById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomFissionContact(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomFissionContacts(array $ids): int;

    /**
     * 查询.
     */
    public function getRoomFissionContactByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getRoomFissionContactByCorpIdStatus(array $corpIds, int $status, array $columns = ['*']): array;

    /**
     * 查询客户信息-unionId&fissionId.
     * @param array|string[] $columns
     */
    public function getRoomFissionContactByRoomIdUnionIdFissionID(int $roomId, string $unionId, int $fissionId = 0, array $columns = ['*']): array;

    /**
     * 查询客户信息-unionId&fissionId.
     * @param array|string[] $columns
     */
    public function getRoomFissionContactByUnionIdFissionID(string $unionId, int $fissionId = 0, array $columns = ['*']): array;

    /**
     * 获取客户助力好友.
     */
    public function getRoomFissionContactByParentUnionId(string $unionId, int $join_status, int $is_new, int $loss, array $columns = ['*']): array;

    /**
     * 统计
     */
    public function countRoomFissionContactByFissionID(int $fissionId, int $join_status, int $status, int $loss, int $roomId, string $day = ''): int;
}
