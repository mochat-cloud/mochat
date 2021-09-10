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
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkTransferLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class LogLogic.
 */
class LogLogic
{
    /**
     * @Inject
     * @var WorkTransferLogContract
     */
    protected $workTransferLogService;

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
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * 获取离职已分配客户.
     * @return array
     */
    public function getModeOneList(array $params)
    {
        //mode：模式 1离职已分配客户 2离职已分配群聊 3在职已分配客户
        if ($params['mode'] == 1) {
            $res = $this->workTransferLogService->getLogByCorpIdStatusTypeName($params['corpId'], 1, 1, $params['name'], $params['employeeId'], $params['createTimeStart'], $params['createTimeEnd']);
        }
        if ($params['mode'] == 2) {
            $res = $this->workTransferLogService->getLogByCorpIdStatusTypeName($params['corpId'], 1, 2, $params['name'], $params['employeeId'], $params['createTimeStart'], $params['createTimeEnd']);
        }
        if ($params['mode'] == 3) {
            $res = $this->workTransferLogService->getLogByCorpIdStatusTypeName($params['corpId'], 2, 1, $params['name'], $params['employeeId'], $params['createTimeStart'], $params['createTimeEnd']);
        }

        $result = [];

        $stateText = [
            1 => '接替完毕',
            2 => '等待接替',
            3 => '客户拒绝',
            4 => '接替成员客户达到上限',
            5 => '无接替记录',
        ];

        foreach ($res as $re) {
            $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdAndWxUserId($params['corpId'], $re['takeoverEmployeeId']);
            $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($params['corpId'], $re['contactId']);

            if ($params['mode'] == 1) {
                $result[] = [
                    'contactId' => $contact['id'],
                    'name' => $contact['name'],
                    'corpName' => $contact['corpName'],
                    'employee' => $employee['name'],
                    'state' => $re['state'] ? $stateText[$re['state']] : '',
                    'createTime' => $re['createdAt'],
                ];
            }

            if ($params['mode'] == 2) {
                $room = $this->workRoomService->getWorkRoomByCorpIdWxChatId($params['corpId'], $re['contactId']);
                $roomNum = $this->workContactRoomService->countWorkContactRoomsByRoomIdContact($room['id']);
                $result[] = [
                    'roomId' => $room['id'],
                    'name' => $re['name'],
                    'employee' => $employee['name'],
                    'roomNum' => $roomNum,
                    'createTime' => $re['createdAt'],
                ];
            }

            if ($params['mode'] == 3) {
                $result[] = [
                    'contactId' => $contact['id'],
                    'name' => $contact['name'],
                    'corpName' => $contact['corpName'],
                    'employee' => $employee['name'],
                    'state' => $re['state'] ? $stateText[$re['state']] : '',
                    'createTime' => $re['createdAt'],
                ];
            }
        }

        return $result;
    }
}
