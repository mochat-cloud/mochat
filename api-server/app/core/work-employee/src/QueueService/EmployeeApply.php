<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\WorkDepartment\Logic\SyncLogic as WorkDepartmentSyncLogic;
use MoChat\App\WorkEmployee\Logic\SyncLogic as WorkEmployeeSyncLogic;

/**
 * 同步企业成员
 * Class EmployeeApply.
 */
class EmployeeApply
{
    /**
     * @var WorkDepartmentSyncLogic
     */
    protected $workDepartmentSyncLogic;

    /**
     * @var WorkEmployeeSyncLogic
     */
    protected $workEmployeeSyncLogic;

    /**
     * @AsyncQueueMessage(pool="employee")
     */
    public function handle(array $corpIds): void
    {
        //部门同步
        (new WorkDepartmentSyncLogic())->handle($corpIds);
        //成员同步
        (new WorkEmployeeSyncLogic())->handle($corpIds);
    }
}
