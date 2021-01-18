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

use App\Contract\BusinessLogServiceInterface;
use App\Model\BusinessLog;
use MoChat\Framework\Service\AbstractService;

class BusinessLogService extends AbstractService implements BusinessLogServiceInterface
{
    /**
     * @var BusinessLog
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getBusinessLogById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getBusinessLogsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getBusinessLogList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createBusinessLog(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createBusinessLogs(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateBusinessLogById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBusinessLog(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBusinessLogs(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据操作人ID.
     * @param array $operationIds 操作人ID
     * @param int $event 事件
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getBusinessLogsByOperationIdEvent(array $operationIds, int $event, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('event', $event)
            ->whereIn('operation_id', $operationIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getBusinessLogsByOperationIdsEvents(array $operationIds, array $events, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('operation_id', $operationIds)
            ->whereIn('event', $events)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }
}
