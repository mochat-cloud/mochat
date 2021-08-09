<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityRecordContract;
use MoChat\Plugin\RoomQuality\Model\RoomQualityRecord;

class RoomQualityRecordService extends AbstractService implements RoomQualityRecordContract
{
    /**
     * @var RoomQualityRecord
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomQualityRecordById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomQualityRecordsById(array $ids, array $columns = ['*']): array
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
    public function getRoomQualityRecordList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomQualityRecord(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomQualityRecords(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomQualityRecordById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomQualityRecord(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomQualityRecords(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getRoomQualityRecordByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getRoomQualityRecordByCorpIdStatus(array $corpIds, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('status', $status)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询数量.
     */
    public function countRoomQualityRecordByQualityId(int $qualityId, int $roomId = 0, string $day = '', string $week = '', string $month = ''): int
    {
        $res = $this->model::query()
            ->where('quality_id', $qualityId);
        if ($roomId > 0) {
            $res->where('room_id', $roomId);
        }
        if (! empty($day)) {
            $res->where('created_at', '>', $day);
        }
        if (! empty($week)) {
            $res->where('created_at', '>', $week);
        }
        if (! empty($month)) {
            $res->where('created_at', '>', $month);
        }
        return $res->count();
    }

    /**
     * 查询最后一条
     * @param array|string[] $columns
     */
    public function getRoomQualityRecordLastByQualityId(int $qualityId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('quality_id', $qualityId)
            ->orderByDesc('id')
            ->first($columns);
        $res || $res = collect([]);

        return $res->toArray();
    }
}
