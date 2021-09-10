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
use MoChat\App\WorkContact\Model\WorkContact;
use MoChat\App\WorkEmployee\Model\WorkEmployee;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;
use MoChat\Plugin\ContactMessageBatchSend\Model\ContactMessageBatchSendResult;

class ContactMessageBatchSendResultService extends AbstractService implements ContactMessageBatchSendResultContract
{
    /**
     * @var ContactMessageBatchSendResult
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendResultById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendResultsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendResultList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSendResult(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSendResults(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactMessageBatchSendResultById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSendResult(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSendResults(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendResultsBySearch(array $params): array
    {
        $batchId = $params['batchId'];
        $sendStatus = $params['sendStatus'] ?? '';
        $keyWords = $params['keyWords'];
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as a')
            ->join(WorkContact::query()->getModel()->getTable() . ' as c', 'a.contact_id', 'c.id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'a.employee_id', 'e.id')
            ->where('a.batch_id', '=', $batchId)
            ->when(is_numeric($sendStatus), function (Builder $query) use ($sendStatus) {
                return $query->where('a.status', '=', $sendStatus);
            })
            ->when(! empty($keyWords), function (Builder $query) use ($keyWords) {
                return $query->where('c.name', 'like', "%{$keyWords}%")
                    ->orWhere('c.nick_name', 'like', "%{$keyWords}%");
            })
            ->paginate($perPage, [
                'a.id',
                'a.status',
                'a.send_time as sendTime',
                'c.id as contactId',
                'c.name as contactName',
                'c.nick_name as contactNickName',
                'c.avatar as contactAvatar',
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
    public function deleteContactMessageBatchSendResultsByBatchId(int $batchId): int
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
    public function getContactMessageBatchSendResultByBatchUserId(int $batchId, string $externalUserUd, array $columns = ['*']): array
    {
        return $this->model::query()
            ->where('batch_id', '=', $batchId)
            ->where('external_user_id', '=', $externalUserUd)
            ->first($columns)
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendResultCount(array $where): int
    {
        return $this->model::query()->where($where)->count();
    }
}
