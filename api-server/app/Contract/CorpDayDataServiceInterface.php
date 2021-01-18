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

interface CorpDayDataServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDataById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDatasById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getCorpDayDataList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createCorpDayData(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createCorpDayDatas(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateCorpDayDataById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteCorpDayData(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteCorpDayDatas(array $ids): int;

    /**
     * 查询多条 - 根据企业id和时间段.
     * @param int $corpId 企业id
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDatasByCorpIdTime(int $corpId, string $startDate, string $endDate, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据企业id和日期.
     * @param int $corpId 企业id
     * @param string $date 日期
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDataByCorpIdDate(int $corpId, string $date, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业id和日期(按日期正序).
     * @param int $corpId 企业id
     * @param array $date 日期
     * @param array|string[] $columns 查询字段
     * @param array $orderBy 排序
     * @param int $limit 数量(若查全部可不传该字段)
     * @return array 数组
     */
    public function getCorpDayDatasByCorpIdDateOther(int $corpId, array $date, array $columns = ['*'], array $orderBy = [['date', 'asc']], int $limit = 0): array;
}
