<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Task;

use MoChat\App\SyncData\Annotation\DynamicCrontab;
use MoChat\App\SyncData\Contract\DataFetcherInterface;
use MoChat\App\SyncData\Task\AbstractSyncDataTask;
use MoChat\App\WorkMessage\Fetcher\MessageDataFetcher;

/**
 * TODO 本功能暂未启用.
 *
 * @DynamicCrontab(name="messageSyncDataTask", scheduleRule="*\/5 * * * *", rule="*\/3 * * * * *", callback="execute", singleton=true)
 */
class MessageSyncDataTask extends AbstractSyncDataTask
{
    protected function getDataFetcher($id = null): DataFetcherInterface
    {
        if (empty($id)) {
            return new MessageDataFetcher();
        }

        return MessageDataFetcher::get((int) $id);
    }
}
