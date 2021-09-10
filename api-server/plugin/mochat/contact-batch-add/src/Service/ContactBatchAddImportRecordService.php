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
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportRecordContract;
use MoChat\Plugin\ContactBatchAdd\Model\ContactBatchAddImportRecord;

class ContactBatchAddImportRecordService extends AbstractService implements ContactBatchAddImportRecordContract
{
    /**
     * @var ContactBatchAddImportRecord
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportRecordById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportRecordsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportRecordList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddImportRecord(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddImportRecords(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddImportRecordById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddImportRecord(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddImportRecords(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddImportRecordsByCorpId(array $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
