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
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;

/**
 * Class TipSopListLogic.
 */
class TipSopListLogic
{
    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var RoomSopContract
     */
    protected $roomSopService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

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
     * @param $params
     * @return array
     */
    public function handle($params)
    {
        $res = $this->roomSopService->getRoomSopByCorpId([$params['corpId']]);
        $result = [];
        foreach ($res as $re) {
            $employees = $this->workRoomService->getWorkRoomsById(json_decode($re['roomIds']));
            $employeeArr = [];
            foreach ($employees as $employee) {
                $employeeArr[] = [
                    'id' => $employee['id'],
                    'wxUserId' => $employee['wxChatId'],
                    'name' => $employee['name'],
                ];
            }
            $result[] = [
                'id' => $re['id'],
                'name' => $re['name'],
                'creatorName' => $this->userService->getUserById($re['creatorId'])['name'],
                'rooms' => $employeeArr,
                'setting' => json_decode($re['setting']),
                'createTime' => $re['createdAt'],
                'state' => $re['state'],
            ];
        }
        return $result;
    }
}
