<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\WorkMessage\Logic\Store;

class StoreApply
{
    /**
     * @AsyncQueueMessage(pool="chat")
     * @param int $corpId 企业ID
     */
    public function handle(int $corpId): void
    {
        (new Store())->handle($corpId, 50);
    }
}
