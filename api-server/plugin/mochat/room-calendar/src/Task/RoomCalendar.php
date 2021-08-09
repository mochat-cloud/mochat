<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\RoomCalendar\Logic\RoomCalendarLogic;

/**
 * @Crontab(name="RoomCalendar", rule="*\/1 * * * * ", callback="execute", memo="这是一个群日历的定时任务")
 */
class RoomCalendar
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        (new RoomCalendarLogic())->handle();
        $this->logger->info('群日历任务' . date('Y-m-d H:i:s', time()));
    }
}
