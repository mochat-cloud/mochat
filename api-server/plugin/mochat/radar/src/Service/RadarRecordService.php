<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Service;

use Hyperf\Database\Model\Builder;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\Radar\Contract\RadarRecordContract;
use MoChat\Plugin\Radar\Model\RadarRecord;

class RadarRecordService extends AbstractService implements RadarRecordContract
{
    /**
     * @var RadarRecord
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRadarRecordById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRadarRecordsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getRadarRecordList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRadarRecord(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRadarRecords(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRadarRecordById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRadarRecord(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRadarRecords(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getRadarRecordByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * @param array|string[] $columns
     */
    public function getRadarRecordByUnionIdChannelIdRadarId(string $unionId, int $channelId, int $radarId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('union_id', $unionId)
            ->where('channel_id', $channelId)
            ->where('radar_id', $radarId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询点击数量.
     */
    public function countRadarRecordPersonByCorpIdRadarIdCreatedAt(int $corpId, int $radarId, string $day): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', $radarId)
            ->where('created_at', '>', $day)
            ->distinct()
            ->count('contact_id');
    }

    /**
     * 查询点击数量.
     */
    public function countRadarRecordByCorpIdRadarIdCreatedAt(int $corpId, int $radarId, string $day): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', $radarId)
            ->where('created_at', '>', $day)
            ->count('id');
    }

    /**
     * 获取渠道点击统计
     */
    public function getRadarRecordByCorpIdRadarIdGroupByChannelId(int $corpId, array $params): array
    {
        $starTime = empty($params['start_time']) ? '' : $params['start_time'];
        $endTime = empty($params['end_time']) ? '' : $params['end_time'];
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', (int) $params['id'])
            ->selectRaw('channel_id')
            ->selectRaw('count(id) as total')
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('created_at', [$starTime, $endTime]);
            })
            ->groupBy(['channel_id'])
            ->get();

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 获取渠道点击人数统计
     */
    public function countRadarRecordByCorpIdRadarIdChannelId(int $corpId, int $channelId, array $params): int
    {
        $starTime = empty($params['start_time']) ? '' : $params['start_time'];
        $endTime = empty($params['end_time']) ? '' : $params['end_time'];
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', (int) $params['id'])
            ->where('channel_id', $channelId)
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('created_at', [$starTime, $endTime]);
            })
            ->distinct()
            ->count('contact_id');
    }

    /**
     * 获取客户点击总次数.
     */
    public function countRadarRecordByCorpIdContactIdSearch(int $corpId, int $contactId, array $params): int
    {
        $channelId = empty($params['channelId']) ? 0 : (int) $params['channelId'];
        $contactName = empty($params['contactName']) ? '' : $params['contactName'];
        $employeeId = empty($params['employeeId']) ? 0 : (int) $params['employeeId'];
        $starTime = empty($params['start_time']) ? '' : $params['start_time'];
        $endTime = empty($params['end_time']) ? '' : $params['end_time'];
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', (int) $params['radar_id'])
            ->where('contact_id', $contactId)
            ->when($channelId > 0, function (Builder $query) use ($channelId) {
                return $query->where('channel_id', $channelId);
            })
            ->when(! empty($contactName), function (Builder $query) {
                return $query->where('nickname', 'like', '%{$contactName}%');
            })
            ->when($employeeId > 0, function (Builder $query) use ($employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('created_at', [$starTime, $endTime]);
            })
            ->count('id');
    }

    /**
     * 获取客户点击详情.
     */
    public function getRadarRecordByCorpIdContactIdSearch(int $corpId, int $contactId, array $params, array $columns = ['*']): array
    {
        $channelId = empty($params['channelId']) ? 0 : (int) $params['channelId'];
        $contactName = empty($params['contactName']) ? '' : $params['contactName'];
        $employeeId = empty($params['employeeId']) ? 0 : (int) $params['employeeId'];
        $starTime = empty($params['start_time']) ? '' : $params['start_time'];
        $endTime = empty($params['end_time']) ? '' : $params['end_time'];
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('radar_id', (int) $params['radar_id'])
            ->where('contact_id', $contactId)
            ->when($channelId > 0, function (Builder $query) use ($channelId) {
                return $query->where('channel_id', $channelId);
            })
            ->when(! empty($contactName), function (Builder $query) {
                return $query->where('nickname', 'like', '%{$contactName}%');
            })
            ->when($employeeId > 0, function (Builder $query) use ($employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('created_at', [$starTime, $endTime]);
            })
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }
}
