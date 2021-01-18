<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Service;

use App\Contract\WorkAgentServiceInterface;
use App\Model\WorkAgent;
use MoChat\Framework\Service\AbstractService;

class WorkAgentService extends AbstractService implements WorkAgentServiceInterface
{
    /**
     * @var WorkAgent
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getWorkAgentById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkAgentsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkAgentList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createWorkAgent(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createWorkAgents(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateWorkAgentById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWorkAgent(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWorkAgents(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkAgentByCorpIdClose(int $corpId, array $columns = ['*']): array
    {
        $data = $this->model::query()->where('close', 0)->where('corp_id', $corpId)->get($columns);
        if (! $data) {
            return [];
        }
        return $data->toArray();
    }
}
