<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class RoomLogic.
 */
class RoomLogic
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
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

    /**
     * 获取离职待分配群列表.
     * @return array
     */
    public function getRoomList(array $params)
    {
        $unassignedList = $this->workUnassignedService->getWorkUnassignedByCorpId([$params['corpId']]);

        $userIdFilter = [];
        foreach ($unassignedList as $item) {
            $userIdFilter[] = $item['handoverUserid'];
        }
        $userIdFilter = array_unique($userIdFilter);

        $res = [];
        foreach ($userIdFilter as $userWxId) {
            $employee = $this->workEmployeeService->getWorkEmployeeByWxUserIdCorpId($userWxId, (int) $params['corpId']);
            $roomList = $this->workRoomService->getWorkRoomsByCorpIdOwnerIds($params['corpId'], [
                'employees' => [$employee['id']],
                'name' => $params['roomName'],
            ]);

            foreach ($roomList as $item) {
                $res[] = [
                    'roomId' => $item['id'],
                    'chatId' => $item['wxChatId'],
                    'roomName' => $item['name'],
                    'owner' => $employee['name'],
                    'userNum' => $this->workContactRoomService->countWorkContactRoomsByRoomIdContact($item['id']),
                    'addNum' => $this->workContactRoomService->countAddWorkContactRoomsByRoomIdTime([$item['id']], date('Y-m-d', time()), date('Y-m-d', time() + 86400)),
                    'quitNum' => $this->workContactRoomService->countQuitWorkContactRoomsByRoomIdTime([$item['id']], date('Y-m-d', time()), date('Y-m-d', time() + 86400)),
                    'createTime' => $item['createdAt'],
                ];
            }
        }

        return $res;
    }
}
