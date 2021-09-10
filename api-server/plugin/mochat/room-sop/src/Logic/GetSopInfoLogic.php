<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopLogContract;

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
     * @var RoomSopLogContract
     */
    protected $roomSopLogService;

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
     * @Inject
     * @var RoomSopContract
     */
    protected $roomSopService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @param $params
     * @return array
     */
    public function handle($params)
    {
        $roomSopLog = $this->roomSopLogService->getRoomSopLogById($params['id']);

        $roomSop = $this->roomSopService->getRoomSopById($roomSopLog['roomSopId']);

        $room = $this->workRoomService->getWorkRoomById($roomSopLog['roomId']);

        $user = $this->userService->getUserById($roomSop['creatorId']);

//        $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($roomSopLog['corpId'], $roomSopLog['contact']);

        $task = json_decode($roomSopLog['task']);

        if ($task->time->type == 0) {
            //1时 30分
            $sec = ((int) $task->time->data->first * 60 * 60) + ((int) $task->time->data->last * 60);
            $tipTime = date('H:i', strtotime($roomSopLog['createdAt']) + $sec);
        } else {
            //1天 11:30
            $tipTime = $task->time->data->last;
        }

        return [
            'creator' => $user['name'],
            'time' => $tipTime,
            'task' => $task,
            'room' => $room,
            'state' => $roomSopLog['state'],
        ];
    }
}
