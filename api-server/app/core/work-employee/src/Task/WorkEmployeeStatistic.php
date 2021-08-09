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

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\QueueService\EmployeeStatisticApply;

/**
 * @Crontab(name="employeeStatistic", rule="* 0 * * *", callback="execute", singleton=true, memo="成员统计拉取")
 */
class WorkEmployeeStatistic
{
    /**
     * @Inject
     * @var EmployeeStatisticApply
     */
    private $employeeStatisticsApply;

    public function execute(): void
    {
        $this->employeeStatisticsApply->handle();
    }
}
