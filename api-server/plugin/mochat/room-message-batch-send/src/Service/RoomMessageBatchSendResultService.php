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
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendResultContract;
use MoChat\Plugin\RoomMessageBatchSend\Model\RoomMessageBatchSendResult;

class RoomMessageBatchSendResultService extends AbstractService implements RoomMessageBatchSendResultContract
{
    /**
     * @var RoomMessageBatchSendResult
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoomMessageBatchSendResult(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoomMessageBatchSendResults(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRoomMessageBatchSendResultById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendResult(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendResults(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultRoomIdsByBatchIds(int $batchId): array
    {
        return $this->model::query()->where('batch_id', '=', $batchId)->pluck('room_id')->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultsBySearch(array $params): array
    {
        $batchId = $params['batchId'];
        $employeeIds = $params['employeeIds'] ?? null;
        $sendStatus = $params['sendStatus'] ?? '';
        $keyWords = $params['keyWords'] ?? '';
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as a')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'a.employee_id', 'e.id')
            ->where('a.batch_id', '=', $batchId)
            ->when(! empty($employeeIds), function (Builder $query) use ($employeeIds) {
                return $query->whereIn('a.employee_id', $employeeIds);
            })
            ->when(is_numeric($sendStatus), function (Builder $query) use ($sendStatus) {
                return $query->where('a.status', '=', $sendStatus);
            })
            ->when(! empty($keyWords), function (Builder $query) use ($keyWords) {
                return $query->where('a.room_name', 'like', "%{$keyWords}%");
            })
            ->paginate($perPage, [
                'a.id',
                'a.status',
                'a.send_time as sendTime',
                'a.room_id as roomId',
                'a.room_name as roomName',
                'a.room_create_time as roomCreateTime',
                'a.room_employee_num as roomEmployeeNum',
                'e.id as employeeId',
                'e.name as employeeName',
                'e.alias as employeeAlias',
            ], 'page', $page);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoomMessageBatchSendResultsByBatchId(int $batchId): int
    {
        return $this->model::query()->where('batch_id', '=', $batchId)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getExternalUserIdsByBatchEmployeeId(int $batchId, int $employeeId): array
    {
        return $this->model::query()
            ->where('batch_id', '=', $batchId)
            ->where('employee_id', '=', $employeeId)
            ->pluck('external_user_id')
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultByBatchRoomId(int $batchId, string $chatId, array $columns = ['*']): array
    {
        return $this->model::query()
            ->where('batch_id', '=', $batchId)
            ->where('chat_id', '=', $chatId)
            ->first($columns)
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomMessageBatchSendResultCount(array $where): int
    {
        return $this->model::query()->where($where)->count();
    }
}
