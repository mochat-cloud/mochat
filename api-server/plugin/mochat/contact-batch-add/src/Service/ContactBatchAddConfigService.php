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
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddConfigContract;
use MoChat\Plugin\ContactBatchAdd\Model\ContactBatchAddConfig;

class ContactBatchAddConfigService extends AbstractService implements ContactBatchAddConfigContract
{
    /**
     * @var ContactBatchAddConfig
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddConfigById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddConfigsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddConfigList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddConfig(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createContactBatchAddConfigs(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddConfigById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddConfig(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContactBatchAddConfigs(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactBatchAddConfigByCorpId(int $corpId, array $values = []): bool
    {
        $attributes['corp_id'] = $corpId;
        $model = $this->model::query();
        if (! $model->where($attributes)->exists()) {
            $values['created_at'] = date('Y-m-d H:i:s'); ## 创建加入创建时间
            return $model->insert(array_merge($attributes, $values));
        }

        return (bool) $model->take(1)->update($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddConfigByCorpId(int $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()->where(['corp_id' => $corpId])->first($columns);
        $res || $res = collect([]);
        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getContactBatchAddConfigOptionWhere(array $where, array $options = [], array $columns = ['*']): array
    {
        $res = $this->model
            ->optionWhere($where, $options)
            ->get($columns);
        return $res->toArray();
    }
}
