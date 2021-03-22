<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochatcloud/mochat/blob/master/LICENSE
 */

namespace App\Service;

use App\Model\ContactMessageBatchSend;
use App\Contract\ContactMessageBatchSendServiceInterface;
use MoChat\Framework\Service\AbstractService;

class ContactMessageBatchSendService extends AbstractService implements ContactMessageBatchSendServiceInterface
{
    /**
     * @var ContactMessageBatchSend
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSend(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactMessageBatchSends(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactMessageBatchSendById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSend(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactMessageBatchSends(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendLockById(int $id, array $columns = ['*']): array
    {
        return $this->model::query()
            ->where('id', '=', $id)
            ->lockForUpdate()
            ->first($columns)
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactMessageBatchSendsBySend(): array
    {
        return $this->model::query()
            ->where('send_way', '=', 2)
            ->where('send_status', '=', 0)
            ->where('definite_time', '<=', date('Y-m-d H:i:s'))
            ->get(['id'])
            ->toArray();
    }

}