<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomRemind\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\RoomRemind\Logic\RoomRemindLogic;

/**
 * @Crontab(name="RoomRemind", rule="*\/1 * * * * ", callback="execute", memo="这是一个客户群提醒的定时任务")
 */
class RoomRemind
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
//        (new RoomRemindApply())->handle();
        (new RoomRemindLogic())->handle();
        $this->logger->info('客户群提醒' . date('Y-m-d H:i:s', time()));
    }
}
