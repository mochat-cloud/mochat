<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\SyncData\Task;

use MoChat\App\SyncData\Contract\DataFetcherInterface;

abstract class AbstractSyncDataTask
{
    public function schedule()
    {
        return $this->getDataSource();
    }

    public function execute($id)
    {
        $data = $this->pull($id);
        if (! empty($data)) {
            $this->syncData($id, $data);
        }
    }

    /**
     * 拉取客户端数据.
     *
     * @param array $dataSource
     * @param mixed $id
     */
    protected function pull($id): array
    {
        return $this->getDataFetcher($id)->pull();
    }

    /**
     * 获取数据源，返回数组中 key 必须是唯一id.
     */
    protected function getDataSource(): array
    {
        return $this->getDataFetcher()->getDataSource();
    }

    /**
     * 获取数据获取客户端.
     *
     * @param int|string $id
     */
    abstract protected function getDataFetcher($id = null): DataFetcherInterface;

    /**
     * 同步数据.
     *
     * @param int|string $id
     *
     * @return mixed
     */
    protected function syncData($id, array $data)
    {
        return $this->getDataFetcher($id)->syncData($data);
    }
}
