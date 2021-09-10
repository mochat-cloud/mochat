<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class GetSopTipInfoLogic.
 */
class GetSopTipInfoLogic
{
    /**
     * @Inject
     * @var ContactSopLogContract
     */
    protected $contactSopLogService;

    /**
     * @Inject
     * @var ContactSopContract
     */
    protected $contactSopService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @var UserContract
     */
    protected $userService;

    /**
     * @param $params
     * @throws \JsonException
     * @return array
     */
    public function handle($params)
    {
        $contactSopLog = $this->contactSopLogService->getContactSopLogList([
            'corp_id' => $params['corpId'],
            'employee' => $params['employeeWxId'],
            'contact' => $params['contactWxId'],
        ]);
        $result = [];

        foreach ($contactSopLog['data'] as $item) {
            $datum = json_decode(json_encode($item));
            ## liu修改
//            $task = json_decode($datum['task'], true, 512, JSON_THROW_ON_ERROR);
//            $datum['task'] = $task;
//            if ($task['time']['type'] === 0) {
//                //1时 30分
//                $sec = ((int)$task['time']['data']['first'] * 60 * 60) + ((int)$task['time']['data']['last']  * 60);
//                $tipTime = date('H:i', strtotime($datum->createdAt) + $sec);
//            } else {
//                //1天 11:30
//                $tipTime = $task['time']['data']['last'];
//            }
            ## xue版本
            $datum->task = json_decode($datum->task);
            if ($datum->task->time->type == 0) {
                //1时 30分
                $sec = ((int) $datum->task->time->data->first * 60 * 60) + ((int) $datum->task->time->data->last * 60);
                $tipTime = date('H:i', strtotime($datum->createdAt) + $sec);
            } else {
                //1天 11:30
                $tipTime = $datum->task->time->data->last;
            }

            $datum->tipTime = $tipTime;

            $result[] = $datum;
        }

        return $result;
    }
}
