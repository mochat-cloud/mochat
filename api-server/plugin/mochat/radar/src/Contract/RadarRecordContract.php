<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Contract;

interface RadarRecordContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRadarRecordById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRadarRecordsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRadarRecordList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRadarRecord(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRadarRecords(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRadarRecordById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRadarRecord(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRadarRecords(array $ids): int;

    /**
     * 查询.
     */
    public function getRadarRecordByCorpId(int $CorpId, array $columns = ['*']): array;

    /**
     * @param array|string[] $columns
     */
    public function getRadarRecordByUnionIdChannelIdRadarId(string $unionId, int $channelId, int $radarId, array $columns = ['*']): array;

    /**
     * 查询点击数量.
     */
    public function countRadarRecordPersonByCorpIdRadarIdCreatedAt(int $corpId, int $radarId, string $day): int;

    /**
     * 查询点击数量.
     */
    public function countRadarRecordByCorpIdRadarIdCreatedAt(int $corpId, int $radarId, string $day): int;

    /**
     * 获取渠道点击次数统计
     */
    public function getRadarRecordByCorpIdRadarIdGroupByChannelId(int $corpId, array $params): array;

    /**
     * 获取渠道点击人数统计
     */
    public function countRadarRecordByCorpIdRadarIdChannelId(int $corpId, int $channelId, array $params): int;

    /**
     * 获取客户点击总次数.
     */
    public function countRadarRecordByCorpIdContactIdSearch(int $corpId, int $contactId, array $params): int;

    /**
     * 获取客户点击详情.
     */
    public function getRadarRecordByCorpIdContactIdSearch(int $corpId, int $contactId, array $params, array $columns = ['*']): array;
}
