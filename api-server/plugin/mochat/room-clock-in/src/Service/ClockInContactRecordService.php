<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactRecordContract;
use MoChat\Plugin\RoomClockIn\Model\ClockInContactRecord;

class ClockInContactRecordService extends AbstractService implements ClockInContactRecordContract
{
    /**
     * @var ClockInContactRecord
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactRecordById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactRecordsById(array $ids, array $columns = ['*']): array
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
    public function getClockInContactRecordList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createClockInContactRecord(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createClockInContactRecords(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContactRecordById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContactRecord(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContactRecords(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getClockInContactRecordByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询总打卡天数.
     */
    public function sumClockInContactRecordTotalDayByClockInId(int $ClockInId): int
    {
        return $this->model::query()
            ->where('total_day', '>', 0)
            ->where('clock_in_id', $ClockInId)
            ->sum('total_day');
    }

    /**
     * 查询总打卡人数-活动id.
     */
    public function countClockInContactRecordByClockInId(int $ClockInId): int
    {
        return $this->model::query()
            ->where('total_day', '>', 0)
            ->where('clock_in_id', $ClockInId)
            ->count('id');
    }

    /**
     * 查询今日打卡人数-活动id.
     * @param int $ClockInId
     */
    public function countInContactRecordTodayByClockInId(int $clockInId): int
    {
        return $this->model::query()
            ->where('day', '>', date('Y-m-d'))
            ->where('clock_in_id', $clockInId)
            ->count('id');
    }

    /**
     * 查询单条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('day', '>', date('Y-m-d'))
            ->where('contact_id', $contactId)
            ->where('clock_in_id', $clockInId)
            ->first($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordsByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId)
            ->where('clock_in_id', $clockInId)
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询单条 -ClockInIdContactId.
     * @param array|string[] $columns
     */
    public function getClockInContactRecordLastByClockInIdContactId(int $clockInId, int $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('contact_id', $contactId)
            ->orderByDesc('day')
            ->first($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }
}
