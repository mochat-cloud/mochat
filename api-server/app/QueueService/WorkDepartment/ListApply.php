<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkDepartment;

use App\Logic\WorkDepartment\SynLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

class ListApply
{
    /**
     * @var SynLogic
     */
    protected $synLogic;

    /**
     * @AsyncQueueMessage(pool="employee").
     */
    public function handle()
    {
        $this->synLogic = make(SynLogic::class);
        return $this->synLogic->handle(['corpIds' => user()['corpIds']]);
    }
}
