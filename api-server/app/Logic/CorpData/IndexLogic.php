<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\CorpData;

use App\Constants\WorkContactEmployee\Status;
use App\Constants\WorkUpdateTime\Type;
use App\Contract\CorpDayDataServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 首页数据统计.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * 企业日数据表.
     * @Inject
     * @var CorpDayDataServiceInterface
     */
    private $dayData;

    /**
     * 客户群.
     * @Inject
     * @var WorkRoomServiceInterface
     */
    private $room;

    /**
     * 客户 - 群关联表.
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    private $contactRoom;

    /**
     * 员工 - 客户关联表.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $contactEmployee;

    /**
     * 员工表.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $employee;

    /**
     * 同步时间表.
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

    /**
     * 企业id.
     * @var int
     */
    private $corpId;

    /**
     * @return array
     */
    public function handle()
    {
        $this->corpId = user()['corpIds'][0];
        //统计总数据
        $totalData = $this->totalData();
        //统计日数据
        $dayData = $this->dayData();
        //统计月数据
        $monthData = $this->monthData();
        //更新时间
        $updateTime = $this->getTime();

        return array_merge($totalData, $dayData, $monthData, $updateTime);
    }

    /**
     * 统计月数据.
     * @return array
     */
    private function monthData()
    {
        //获取本月第一天及最后一天.
        $beginDate = date('Y-m-01', strtotime(date('Y-m-d')));
        $endDate   = date('Y-m-d', strtotime("{$beginDate} +1 month -1 day"));

        //查询本月数据
        $month = $this->dayData->getCorpDayDatasByCorpIdTime((int) $this->corpId, $beginDate, $endDate);

        //获取上月第一天和最后一天
        $lastBeginDate = date('Y-m-01', strtotime('-1 month'));
        $lastEndDate   = date('Y-m-t', strtotime('-1 month'));

        //查询上月数据
        $lastMonth = $this->dayData->getCorpDayDatasByCorpIdTime((int) $this->corpId, $lastBeginDate, $lastEndDate);

        return [
            //本月新增客户数
            'addFriendsNum' => empty($month) ? 0 : array_sum(array_column($month, 'addContactNum')),
            //上月累计新增客户数
            'lastAddFriendsNum' => empty($lastMonth) ? 0 : array_sum(array_column($lastMonth, 'addContactNum')),
            //本月新增社群数
            'monthAddRoomNum' => empty($month) ? 0 : array_sum(array_column($month, 'addRoomNum')),
            //上月累计新增社群数
            'lastMonthAddRoomNum' => empty($lastMonth) ? 0 : array_sum(array_column($lastMonth, 'addRoomNum')),
            //本月新增群成员数
            'monthAddRoomMemberNum' => empty($month) ? 0 : array_sum(array_column($month, 'addIntoRoomNum')),
            //上月累计新增群成员数
            'lastMonthAddRoomMemberNum' => empty($lastMonth) ? 0 : array_sum(array_column($lastMonth, 'addIntoRoomNum')),
            //本月流失客户数
            'monthLossContactNum' => empty($month) ? 0 : array_sum(array_column($month, 'lossContactNum')),
            //上月累计流失客户数
            'lastMonthLossContactNum' => empty($lastMonth) ? 0 : array_sum(array_column($lastMonth, 'lossContactNum')),
        ];
    }

    /**
     * 统计日数据.
     * @return array
     */
    private function dayData()
    {
        //查询今日数据
        $day = $this->dayData->getCorpDayDataByCorpIdDate((int) $this->corpId, date('Y-m-d'));

        //查询昨日数据
        $lastDay = $this->dayData->getCorpDayDataByCorpIdDate((int) $this->corpId, date('Y-m-d', strtotime('-1 day')));
        return [
            //今日新增客户数
            'addContactNum' => empty($day['addContactNum']) ? 0 : $day['addContactNum'],
            //昨日新增客户数
            'lastAddContactNum' => empty($lastDay['addContactNum']) ? 0 : $lastDay['addContactNum'],
            //今日新增入群数
            'addIntoRoomNum' => empty($day['addIntoRoomNum']) ? 0 : $day['addIntoRoomNum'],
            //昨日新增入群数
            'lastAddIntoRoomNum' => empty($lastDay['addIntoRoomNum']) ? 0 : $lastDay['addIntoRoomNum'],
            //今日流失客户数
            'lossContactNum' => empty($day['lossContactNum']) ? 0 : $day['lossContactNum'],
            //昨日流失客户数
            'lastLossContactNum' => empty($lastDay['lossContactNum']) ? 0 : $lastDay['lossContactNum'],
            //今日退群数
            'quitRoomNum' => empty($day['quitRoomNum']) ? 0 : $day['quitRoomNum'],
            //昨日退群数
            'lastQuitRoomNum' => empty($lastDay['quitRoomNum']) ? 0 : $lastDay['quitRoomNum'],
        ];
    }

    /**
     * 统计总数据.
     * @return array
     */
    private function totalData()
    {
        //总微信客户数
        $totalContact = $this->contactEmployee->countWorkContactEmployeesByCorpId((int) $this->corpId, [Status::NORMAL]);
        //总微信群数
        $totalRooms = $this->room->countWorkRoomByCorpIds([$this->corpId]);
        //查询企业下所有的群
        $allRoom = $this->room->getWorkRoomsByCorpId((int) $this->corpId, ['id']);
        if (! empty($allRoom)) {
            $roomIds = array_column($allRoom, 'id');
            //总群成员数
            $totalMember = $this->contactRoom->countWorkContactRoomByRoomIds($roomIds);
        }
        //总企业有效成员数
        $totalEmployee = $this->employee->countWorkEmployeesByCorpId((int) $this->corpId);

        return [
            'weChatContactNum' => $totalContact,
            'weChatRoomNum'    => $totalRooms,
            'roomMemberNum'    => empty($totalMember) ? 0 : $totalMember,
            'corpMemberNum'    => $totalEmployee,
        ];
    }

    /**
     * 获取最后一次同步客户的时间.
     * @return mixed
     */
    private function getTime()
    {
        $type = Type::CORP_DATA;

        $res = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType([$this->corpId], (int) $type, ['last_update_time']);

        $data['updateTime'] = empty($res) ? '' : end($res)['lastUpdateTime'];

        return $data;
    }
}
