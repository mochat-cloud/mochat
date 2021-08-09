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
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;
use MoChat\Plugin\ContactBatchAdd\Model\ContactBatchAddImport;

class ContactBatchAddImportService extends AbstractService implements ContactBatchAddImportContract
{
    /**
     * @var ContactBatchAddImport
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddImport(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddImports(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddImportById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddImport(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddImports(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastId(): int
    {
        return (int) $this->model->orderBy('id', 'desc')->value('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportOptionWhere(array $where, array $options = [], array $columns = ['*']): array
    {
        $res = $this->model
            ->optionWhere($where, $options)
            ->get($columns);
        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddImports(array $values): int
    {
        return $this->model->batchUpdateByIds($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportOptionWhereGroup(array $where, array $groupBy, array $columns = ['*']): array
    {
        $res = $this->model
            ->optionWhere($where)
            ->groupBy($groupBy)
            ->get($columns);
        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportOptionWhereCount(array $where): int
    {
        return $this->model
            ->optionWhere($where)
            ->count();
    }
}
