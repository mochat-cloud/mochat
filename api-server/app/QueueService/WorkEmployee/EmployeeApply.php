<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkEmployee;

use App\Logic\WorkDepartment\SynLogic as WorkDepartmentSynLogic;
use App\Logic\WorkEmployee\SynLogic as WorkEmployeeSynLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

/**
 * 同步企业成员
 * Class EmployeeApply.
 */
class EmployeeApply
{
    /**
     * @var WorkDepartmentSynLogic
     */
    protected $workDepartmentSynLogic;

    /**
     * @var WorkEmployeeSynLogic
     */
    protected $workEmployeeSynLogic;

    /**
     * @AsyncQueueMessage(pool="employee")
     */
    public function handle(array $corpIds): void
    {
        //部门同步
        (new WorkDepartmentSynLogic())->handle($corpIds);
        //成员同步
        (new WorkEmployeeSynLogic())->handle($corpIds);
    }
}
