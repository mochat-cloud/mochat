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

use Hyperf\Database\Model\Builder;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;
use MoChat\Plugin\RoomClockIn\Model\ClockIn;

class ClockInService extends AbstractService implements ClockInContract
{
    /**
     * @var ClockIn
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getClockInsById(array $ids, array $columns = ['*']): array
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
    public function getClockInList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createClockIn(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createClockIns(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateClockInById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteClockIn(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteClockIns(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getClockInByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 多条分页.
     * @return array 分页结果 Hyper\Paginator\Paginator::toArray
     */
    public function getClockInListBySearch(array $user, array $params): array
    {
        $corpId = $user['corpIds'][0];
        $status = (int) $params['status'];
        $name = ! isset($params['name']) ? $params['name'] : '';
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];
        $date = date('Y-m-d H:i:s');
        $create_user_id = $user['id'];

        $data = $this->model::from($this->model::query()->getModel()->getTable())
            ->where('corp_id', '=', $corpId)
            ->where('create_user_id', '=', $create_user_id)
            ->when($status === 1, function (Builder $query) use ($date) {
                return $query->whereRaw('(`time_type`=1 or (`start_time`<? and `end_time`>?))', [$date, $date]);
            })
            ->when($status === 2, function (Builder $query) use ($date) {
                return $query->where([['time_type', '=', '2'], ['start_time', '>',  $date]]);
            })
            ->when($status === 3, function (Builder $query) use ($date) {
                return $query->where([['time_type', '=', '2'], ['end_time', '<',  $date]]);
            })
            ->when(isset($name), function (Builder $query) use ($name) {
                return $query->where('name', 'like', "%{$name}%");
            })
            ->paginate($perPage, [
                'id', 'name', 'start_time', 'end_time', 'type', 'time_type', 'contact_clock_tags', 'create_user_id', 'created_at',
            ], 'page', $page);

        $data || $data = collect([]);
        return $data->toArray();
    }
}
