<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Service;

use Hyperf\Database\Model\Builder;
use MoChat\App\WorkEmployee\Model\WorkEmployee;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomMessageBatchSend\Constants\Status;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendEmployeeContract;
use MoChat\Plugin\RoomMessageBatchSend\Model\RoomMessageBatchSendEmployee;

class RoomMessageBatchSendEmployeeService extends AbstractService implements RoomMessageBatchSendEmployeeContract
{
    /**
     * @var RoomMessageBatchSendEmployee
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeesById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoomMessageBatchSendEmployee(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoomMessageBatchSendEmployees(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRoomMessageBatchSendEmployeeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendEmployee(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendEmployees(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeesBySearch(array $params): array
    {
        $batchId = $params['batchId'];
        $employeeIds = $params['employeeIds'] ?? null;
        $sendStatus = $params['sendStatus'] ?? null;
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as a')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'a.employee_id', 'e.id')
            ->where('a.batch_id', '=', $batchId)
            ->when(is_numeric($sendStatus), function (Builder $query) use ($sendStatus) {
                return $query->where('a.status', '=', $sendStatus);
            })
            ->when(! empty($employeeIds), function (Builder $query) use ($employeeIds) {
                return $query->whereIn('employee_id', $employeeIds);
            })
            ->paginate($perPage, [
                'a.id',
                'a.status as status',
                'a.send_time as sendTime',
                'a.send_room_total as sendRoomTotal',
                'e.id as employeeId',
                'e.name as employeeName',
                'e.alias as employeeAlias',
                'e.avatar as employeeAvatar',
                'e.thumb_avatar as employeeThumbAvatar',
            ], 'page', $page);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeesByBatchId(int $batchId, array $ids = [], array $columns = ['*']): array
    {
        return $this->model::query()
            ->where('batch_id', '=', $batchId)
            ->when(! empty($ids), function (Builder $query) use ($ids) {
                return $query->whereIn('id', $ids);
            })
            ->get($columns)
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeesByResultSync(int $minutes, int $expireMinutes, int $limit = 1000): array
    {
        return $this->model::query()
            ->where('last_sync_time', '<', date('Y-m-d H:i:s', time() - $minutes * 60))
            ->where('status', '=', Status::NOT_SEND)
            ->where('created_at', '>', date('Y-m-d H:i:s', time() - $expireMinutes * 60))
            ->limit($limit)
            ->get(['id'])
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendEmployeesByBatchId(int $batchId): int
    {
        return $this->model::query()->where('batch_id', '=', $batchId)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeeLockById(int $id, array $columns = ['*']): array
    {
        return $this->model::query()->where('id', '=', $id)->lockForUpdate()->first($columns)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendEmployeeCount(array $where = []): int
    {
        return $this->model::query()->where($where)->count();
    }

    /**
     * 查询最近一周的群发消息.
     */
    public function getContactMessageBatchSendEmployeeIdsByLastWeek(): array
    {
        return $this->model::query()
            ->where('receive_status', 1)
            ->where('created_at', '>=', date('Y-m-d', time() - 60 * 60 * 24 * 7) . ' 00:00:00')
            ->pluck('id')
            ->toArray();
    }
}
