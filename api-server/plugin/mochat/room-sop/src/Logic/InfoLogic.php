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
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;

/**
 * Class InfoLogic.
 */
class InfoLogic
{
    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

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
        $res = $this->roomSopService->getRoomSopById((int) $params['id']);

        $employees = $this->workRoomService->getWorkRoomsById(json_decode($res['roomIds']));

        $employeeArr = [];

        foreach ($employees as $item) {
            $employeeArr[] = [
                'roomId' => $item['id'],
                'name' => $item['name'],
                'num' => $this->workContactRoomService->countWorkContactRoomsByRoomIdContact($item['id']),
                'ownerName' => $this->workEmployeeService->getWorkEmployeeById($item['ownerId'])['name'],
                'wxChatId' => $item['wxChatId'],
            ];
        }
        ## 处理创建者信息
        $username = $this->userService->getUserById($res['creatorId']);
        return [
            'id' => $res['id'],
            'name' => $res['name'],
            'creatorName' => isset($username['name']) ? $username['name'] : '',
            'rooms' => $employeeArr,
            'setting' => json_decode($res['setting']),
            'chatHandleNum' => count(json_decode($res['roomIds'])),
            'todayTipNum' => 0,
            'ownerTipNum' => 0,
            'createTime' => $res['createdAt'],
        ];
    }
}
