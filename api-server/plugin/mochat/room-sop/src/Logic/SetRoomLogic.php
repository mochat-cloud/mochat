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

use Hyperf\Contract\StdoutLoggerInterface;
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
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopLogContract;
use MoChat\Plugin\RoomSop\QueueService\RoomSopPush;

/**
 * Class SetRoomLogic.
 */
class SetRoomLogic
{
    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var RoomSopPush
     */
    protected $roomSopPush;

    /**
     * @Inject
     * @var RoomSopContract
     */
    protected $roomSopService;

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
     * @Inject
     * @var RoomSopLogContract
     */
    protected $roomSopLogService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param $params
     * @return bool
     */
    public function handle($params)
    {
        $roomSop = $this->roomSopService->getRoomSopById((int) $params['id']);
        $userInfo = $this->userService->getUserById($roomSop['creatorId']);
        $params['userName'] = $userInfo['name'];

        $before = json_decode($roomSop['roomIds']);
        $after = json_decode($params['employees']);

        if (count($before) < count($after)) {
            //增加了
            $differ = array_diff($after, $before);

            $this->logger->error('群SOP增加群聊');

            foreach ($differ as $item) {
                $room = $this->workRoomService->getWorkRoomById($item);
                $employee = $this->workEmployeeService->getWorkEmployeeById($room['ownerId']);

                $taskList = json_decode($roomSop['setting']);
                $currentTime = time();
                $currentDayZeroTime = strtotime(date('Y-m-d') . ' 00:00:00');
                foreach ($taskList as $task) {
                    $delay = 0;
                    if ($task->time->type == 0) {
                        // 1时 30分
                        $delay += (int) $task->time->data->first * 3600;
                        $delay += (int) $task->time->data->last * 60;
                    } else {
                        // 1天 11:30
                        $delayDate = date('Y-m-d', $currentDayZeroTime + (int) $task->time->data->first * 86400);
                        $delayTime = strtotime($delayDate . ' ' .  $task->time->data->last . ':00');
                        $delay = $delayTime - $currentTime;
                    }

                    //添加触发记录
                    $sopLogId = $this->roomSopLogService->createRoomSopLog([
                        'corp_id' => $params['corpId'],
                        'room_sop_id' => $roomSop['id'],
                        'room_id' => $room['id'],
                        'employee' => $employee['wxUserId'],
                        'task' => json_encode($task),
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ]);

                    $flag = $this->roomSopPush->push([
                        'corpId' => $params['corpId'],
                        'employeeWxId' => $employee['wxUserId'],
                        'roomSopLogId' => $sopLogId,
                        'sopCreatorName' => $params['userName'],
                    ], $delay);
                }
            }
        }

//        if (count($before) > count($after)) {
//            //减少了
//            $differ = array_diff($before, $after);
//        }

        return $this->roomSopService->updateRoomSopById((int) $params['id'], ['room_ids' => $params['employees']]) ? true : false;
    }
}
