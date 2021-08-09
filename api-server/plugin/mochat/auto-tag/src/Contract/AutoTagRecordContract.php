<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Contract;

interface AutoTagRecordContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getAutoTagRecordById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getAutoTagRecordsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getAutoTagRecordList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createAutoTagRecord(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createAutoTagRecords(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateAutoTagRecordById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteAutoTagRecord(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteAutoTagRecords(array $ids): int;

    /**
     * 查询.
     */
    public function getAutoTagRecordByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * 查询单条
     * @param array|string[] $columns
     */
    public function getAutoTagRecordByCorpIdWxExternalUseridAutoTagId(int $corpId, string $wxExternalUserid, int $autoTagId, int $tagRuleId, array $columns = ['*']): array;

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getAutoTagRecordByKeyWordSearch(array $params): array;

    /**
     * 查询客户id为空记录.
     * @param array|string[] $columns
     */
    public function getAutoTagRecord(array $columns = ['*']): array;

    /**
     * 查询打标签客户总数-corpId-autoTagId.
     */
    public function countAutoTagRecordByCorpIdAutoTagId(int $corpId, int $autoTagId): int;

    /**
     * 查询今日打标签客户总数-corpId-autoTagId.
     */
    public function countAutoTagRecordTodayByCorpIdAutoTagId(int $corpId, int $autoTagId): int;

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getAutoTagRecordByRoomSearch(array $params): array;

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getAutoTagRecordByTimeSearch(array $params): array;
}
