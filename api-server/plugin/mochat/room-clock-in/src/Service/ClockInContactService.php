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
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;
use MoChat\Plugin\RoomClockIn\Model\ClockInContact;

class ClockInContactService extends AbstractService implements ClockInContactContract
{
    /**
     * @var ClockInContact
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInContactsById(array $ids, array $columns = ['*']): array
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
    public function getClockInContactList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createClockInContact(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createClockInContacts(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContactById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 修改多条 - 根据IDs.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInContact(array $ids, array $data): int
    {
        return $this->model::query()->whereIn('id', $ids)->update($data);
//        return $this->model->batchUpdateByIds($values);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContact(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteClockInContacts(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getClockInContactByCorpId(int $CorpId, array $columns = ['*']): array
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
    public function sumClockInContactTotalDayByClockInId(int $ClockInId): int
    {
        $res = $this->model::query()
            ->where('total_day', '>', 0)
            ->where('clock_in_id', $ClockInId)
            ->sum('total_day');
        return $res > 0 ? (int) $res : 0;
    }

    /**
     * 查询总打卡人数-活动id.
     */
    public function countClockInContactByClockInId(int $ClockInId): int
    {
        return $this->model::query()
            ->where('total_day', '>', 0)
            ->where('clock_in_id', $ClockInId)
            ->count('id');
    }

    /**
     * 查询单条-clockInId&unionId.
     * @param array|string[] $columns
     */
    public function getClockInContactByClockInIdUnionId(int $clockInId, string $unionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('union_id', $unionId)
            ->first($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条-groupSeriesDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupSeriesDay(int $clockInId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('series_day', '>', 0)
            ->selectRaw('series_day')
            ->selectRaw('count(id) as total')
            ->groupBy(['series_day'])
            ->orderByDesc('series_day')
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条-groupTotalDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupTotalDay(int $clockInId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('total_day', '>', 0)
            ->selectRaw('total_day')
            ->selectRaw('count(id) as total')
            ->groupBy(['total_day'])
            ->orderByDesc('total_day')
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询10条-groupSeriesDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupSeriesDayLimit(int $clockInId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('series_day', '>', 0)
            ->groupBy(['series_day'])
            ->orderByDesc('series_day')
            ->limit(10)
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询10条-groupTotalDay.
     * @param array|string[] $columns
     */
    public function getClockInContactListGroupTotalDayLimit(int $clockInId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->where('total_day', '>', 0)
            ->groupBy(['union_id', 'total_day'])
            ->orderByDesc('total_day')
            ->limit(10)
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * @param array|string[] $columns
     */
    public function getClockInContactCityById(int $clockInId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('clock_in_id', $clockInId)
            ->distinct()
            ->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }
}
