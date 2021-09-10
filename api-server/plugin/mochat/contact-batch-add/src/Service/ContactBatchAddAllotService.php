<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddAllotContract;
use MoChat\Plugin\ContactBatchAdd\Model\ContactBatchAddAllot;

class ContactBatchAddAllotService extends AbstractService implements ContactBatchAddAllotContract
{
    /**
     * @var ContactBatchAddAllot
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddAllotById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddAllotsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddAllotList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddAllot(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddAllots(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddAllotById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddAllot(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddAllots(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddAllotOptionWhereGroup(array $where, array $groupBy, array $columns = ['*']): array
    {
        $res = $this->model
            ->optionWhere($where)
            ->groupBy($groupBy)
            ->get($columns);
        return $res->toArray();
    }
}
