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
 * Class TipSopAddRoomLogic.
 */
class TipSopAddRoomLogic
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
     * @throws \JsonException
     * @return array
     */
    public function handle($params)
    {
        ## 清除原有sop
        $sop = $this->roomSopService->getRoomSopByCorpIdRoomId($params['corpId'], $params['roomId'], ['id', 'room_ids']);
        foreach ($sop as $item) {
            $room_ids = json_decode($item['roomIds'], true, 512, JSON_THROW_ON_ERROR);
            $index = array_search($params['roomId'], $room_ids);
            if ($index !== false) {
                array_splice($room_ids, $index, 1);
                $this->roomSopService->updateRoomSopById($item['id'], [
                    'room_ids' => json_encode($room_ids, JSON_THROW_ON_ERROR),
                ]);
            }
        }
        ## 设置sop
        $sop = $this->roomSopService->getRoomSopById($params['id']);
        $rooms = json_decode($sop['roomIds'], true, 512, JSON_THROW_ON_ERROR);
        $rooms[] = $params['roomId'];
        $rooms = array_unique($rooms);

        $this->roomSopService->updateRoomSopById($params['id'], [
            'room_ids' => json_encode($rooms, JSON_THROW_ON_ERROR),
        ]);

        return [];
    }
}
