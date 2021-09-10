<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Service;

use Hyperf\Database\Model\Builder;
use MoChat\App\WorkRoom\Model\WorkRoom;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use MoChat\Plugin\RoomFission\Model\RoomFissionRoom;

class RoomFissionRoomService extends AbstractService implements RoomFissionRoomContract
{
    /**
     * @var RoomFissionRoom
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomFissionRoomById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomFissionRoomsById(array $ids, array $columns = ['*']): array
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
    public function getRoomFissionRoomList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomFissionRoom(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomFissionRooms(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomFissionRoomById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomFissionRoom(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomFissionRooms(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getRoomFissionRoomByCorpId(int $CorpId, array $columns = ['*']): array
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
    public function getRoomFissionRoomByCorpIdStatus(array $corpIds, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('status', $status)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 修改一条-fissionId.
     */
    public function updateRoomFissionRoomByFissionIdRoomId(int $fissionId, int $roomId, array $data): int
    {
        return $this->model::query()
            ->where('fission_id', $fissionId)
            ->where('room->id', $roomId)
            ->update($data);
    }

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getRoomFissionRoomByFissionId(int $fissionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('fission_id', $fissionId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /*查询多条
     * @param $params
     * @return array
     */
    public function getRoomFissionRoomBySearch($params): array
    {
        $room_name = empty($params['room_name']) ? '' : $params['room_name'];
        $owner_id = empty($params['owner_id']) ? '' : (int) $params['owner_id'];
        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as room_fission_room')
            ->join(WorkRoom::query()->getModel()->getTable() . ' as wr', 'room_fission_room.room->id', 'wr.id')
            ->where('room_fission_room.fission_id', (int) $params['id'])
            ->when(! empty($params['room_name']), function (Builder $query) use ($room_name) {
                return $query->where('room_fission_room.room->name', 'like', '%' . $room_name . '%');
            })
            ->when(! empty($owner_id), function (Builder $query) use ($owner_id) {
                return $query->where('wr.owner_id', $owner_id);
            })
            ->get([
                'room_fission_room.room->id as id',
                'room_fission_room.room->name as name',
                'wr.owner_id',
            ]);

        $data || $data = collect([]);
        return $data->toArray();
    }
}
