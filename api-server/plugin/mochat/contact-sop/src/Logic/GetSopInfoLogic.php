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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class GetSopInfoLogic.
 */
class GetSopInfoLogic
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
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @param $params
     * @return array
     */
    public function handle($params)
    {
        $contactSopLog = $this->contactSopLogService->getContactSopLogById($params['id']);
        if (empty($contactSopLog)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, 'sop记录不存在');
        }

        $contactSop = $this->contactSopService->getContactSopById($contactSopLog['contactSopId']);
        if (empty($contactSop)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, 'sop记录不存在');
        }

        $user = $this->userService->getUserById($contactSop['creatorId']);
        if (empty($user)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '用户不存在');
        }

        $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($contactSopLog['corpId'], $contactSopLog['contact']);
        if (empty($contact)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '客户不存在');
        }

        $contact['avatar'] = file_full_url($contact['avatar']);

        $contactSop['contactIds'];

        $task = json_decode($contactSopLog['task']);

        $delay = 0;
        if ($task->time->type == 0) {
            //1时 30分
            $delay += (int) $task->time->data->first * 3600;
            $delay += (int) $task->time->data->last * 60;
            $tipTime = date('Y-m-d H:i', strtotime($contact['createdAt']) + $delay);
        } else {
            //1天 11:30
            $delay += (int) $task->time->data->first * 86400;
            $tipTime = date('Y-m-d', strtotime($contact['createdAt']) + $delay) . " {$task->time->data->last}";
        }

        if ($task->time->type == 0) {
            $time = "{$task->time->data->first}分{$task->time->data->last}秒";
        } else {
            $time = "{$task->time->data->first}天后的{$task->time->data->last}";
        }

        return [
            'creator' => $user['name'],
            'time' => $time,
            'tipTime' => $tipTime,
            'task' => $task,
            'contact' => $contact,
        ];
    }
}
