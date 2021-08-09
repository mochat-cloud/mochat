<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkDepartment\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\WorkDepartment\Logic\SyncLogic;

class ListApply
{
    /**
     * @var SyncLogic
     */
    protected $syncLogic;

    /**
     * @AsyncQueueMessage(pool="employee").
     */
    public function handle()
    {
        $this->syncLogic = make(SyncLogic::class);
        return $this->syncLogic->handle(['corpIds' => user()['corpIds']]);
    }
}
