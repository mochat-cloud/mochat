<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Service;

use Hyperf\Database\Model\Builder;
use MoChat\App\WorkEmployee\Model\WorkEmployee;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ContactMessageBatchSend\Constants\Status;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Model\ContactMessageBatchSendEmployee;

class ContactMessageBatchSendEmployeeService extends AbstractService implements ContactMessageBatchSendEmployeeContract
{
    /**
     * @var ContactMessageBatchSendEmployee
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeesById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSendEmployee(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSendEmployees(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactMessageBatchSendEmployeeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSendEmployee(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSendEmployees(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeesBySearch(array $params): array
    {
        $batchId = $params['batchId'];
        $sendStatus = $params['sendStatus'];
        $keyWords = $params['keyWords'];
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as a')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'a.employee_id', 'e.id')
            ->where('a.batch_id', '=', $batchId)
            ->when(is_numeric($sendStatus), function (Builder $query) use ($sendStatus) {
                return $query->where('a.status', '=', $sendStatus);
            })
            ->when(! empty($keyWords), function (Builder $query) use ($keyWords) {
                return $query->where('e.name', 'like', "%{$keyWords}%");
            })
            ->paginate($perPage, [
                'a.id',
                'a.status as status',
                'a.send_time as sendTime',
                'a.send_contact_total as sendContactTotal',
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
    public function getContactMessageBatchSendEmployeesByBatchId(int $batchId, array $ids = [], array $columns = ['*']): array
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
    public function getContactMessageBatchSendEmployeesByResultSync(int $minutes, int $expireMinutes, int $limit = 1000): array
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
    public function deleteContactMessageBatchSendEmployeesByBatchId(int $batchId): int
    {
        return $this->model::query()->where('batch_id', '=', $batchId)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeeLockById(int $id, array $columns = ['*']): array
    {
        return $this->model::query()->where('id', '=', $id)->lockForUpdate()->first($columns)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendEmployeeCount(array $where = []): int
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
