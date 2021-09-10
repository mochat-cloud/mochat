<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\QueueService\EmployeeStatisticApply;

/**
 * @Crontab(name="employeeStatistic", rule="30 0 * * *", callback="execute", singleton=true, memo="成员统计拉取")
 */
class WorkEmployeeStatistic
{
    /**
     * @Inject
     * @var EmployeeStatisticApply
     */
    private $employeeStatisticsApply;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        try {
            $this->employeeStatisticsApply->handle();
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '成员统计拉取任务失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
