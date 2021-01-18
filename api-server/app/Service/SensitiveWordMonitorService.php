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

use App\Contract\SensitiveWordMonitorServiceInterface;
use App\Model\SensitiveWordMonitor;
use MoChat\Framework\Service\AbstractService;

class SensitiveWordMonitorService extends AbstractService implements SensitiveWordMonitorServiceInterface
{
    /**
     * @var SensitiveWordMonitor
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getSensitiveWordMonitorById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getSensitiveWordMonitorsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getSensitiveWordMonitorList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createSensitiveWordMonitor(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createSensitiveWordMonitors(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSensitiveWordMonitorById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSensitiveWordMonitor(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSensitiveWordMonitors(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 敏感词词库统计
     * @param array $sensitiveWordId 敏感词词库id
     * @param int $source 触发来源 1-员工 2-客户
     */
    public function countBySensitiveWordIdSource(array $sensitiveWordId, int $source): array
    {
        $sensitiveWordIdStr = implode(',', $sensitiveWordId);
        $res                = $this->model
            ->where('source', $source)
            ->havingRaw('sensitive_word_id in (' . $sensitiveWordIdStr . ')')
            ->selectRaw('sensitive_word_id')
            ->selectRaw('count(id) as total')
            ->groupBy('sensitive_word_id')
            ->get();

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }
}
