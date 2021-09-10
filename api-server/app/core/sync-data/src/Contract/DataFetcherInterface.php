<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\SyncData\Contract;

interface DataFetcherInterface
{
    /**
     * 从远端拉取数据，然后更新数据.
     */
    public function pull(int $limit = 0): array;

    /**
     * 同步数据.
     */
    public function syncData(array $data);

    /**
     * 获取数据源，返回数组中 key 必须是唯一id.
     */
    public function getDataSource(): array;
}
