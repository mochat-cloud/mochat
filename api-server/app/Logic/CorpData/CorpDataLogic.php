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

use App\Constants\WorkUpdateTime\Type;
use App\Contract\CorpDayDataServiceInterface;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 更新首页数据脚本.
 *
 * Class CorpDataLogic
 */
class CorpDataLogic
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

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
     * 同步时间表.
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

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
     * 企业表.
     * @Inject
     * @var CorpServiceInterface
     */
    private $corp;

    public function handle()
    {
        //查询所有企业
        $corp = $this->corp->getCorps(['id']);
        if (empty($corp)) {
            return [];
        }

        //处理企业数据
        foreach ($corp as $val) {
            //计算企业当日新增数据 并插入日数据表
            $this->handleDayData($val['id']);
            //记录更新时间
            $this->updateTime($val['id']);
        }

        return [];
    }

    /**
     * 计算企业当日新增数据 并插入日数据表.
     * @param $corpId
     */
    private function handleDayData($corpId)
    {
        $startTime = date('Y-m-d') . ' 00:00:00';
        $endTime   = date('Y-m-d') . ' 23:59:59';

        //当日新增客户数
        $contact = $this->contactEmployee->countWorkContactEmployeesByCorpIdTime((int) $corpId, $startTime, $endTime);
        //今日新增社群数
        $room = $this->room->countAddWorkRoomsByCorpIdTime((int) $corpId, $startTime, $endTime);
        //查询企业下所有的群
        $allRoom = $this->room->getWorkRoomsByCorpId((int) $corpId, ['id']);
        if (! empty($allRoom)) {
            $roomIds = array_column($allRoom, 'id');
            //当日新增入群数
            $intoRoom = $this->contactRoom->countAddWorkContactRoomsByRoomIdTime($roomIds, $startTime, $endTime);
            //今日退群人数
            $outRoom = $this->contactRoom->countQuitWorkContactRoomsByRoomIdTime($roomIds, $startTime, $endTime);
        }

        //今日流失客户
        $lossContact = $this->contactEmployee->countLossWorkContactEmployeesByCorpIdTime((int) $corpId, $startTime, $endTime);

        $data = [
            'corp_id'           => $corpId,
            'add_contact_num'   => $contact,
            'add_room_num'      => $room,
            'add_into_room_num' => empty($intoRoom) ? 0 : $intoRoom,
            'loss_contact_num'  => $lossContact,
            'quit_room_num'     => empty($outRoom) ? 0 : $outRoom,
            'date'              => date('Y-m-d'),
        ];

        //查询日数据表中是否已有今日数据
        $dayInfo = $this->dayData->getCorpDayDataByCorpIdDate((int) $corpId, date('Y-m-d'));
        if (! empty($dayInfo)) {
            //更新
            $updateDayData = $this->dayData->updateCorpDayDataById((int) $dayInfo['id'], $data);
            if (! is_int($updateDayData)) {
                $this->logger->error(sprintf('%s [%s] %s', '更新企业日数据失败', date('Y-m-d H:i:s')), $data);
            }
        } else {
            //新增
            $dayCreateRes = $this->dayData->createCorpDayData($data);
            if (! is_int($dayCreateRes)) {
                $this->logger->error(sprintf('%s [%s] %s', '新增企业日数据失败', date('Y-m-d H:i:s')), $data);
            }
        }
    }

    /**
     * 记录更新时间.
     * @param $corpId
     */
    private function updateTime($corpId)
    {
        //查询当前企业有没有同步客户的时间
        $workUpdateTime = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType([$corpId], (int) Type::CORP_DATA);
        //如果查到 就更新
        if (! empty($workUpdateTime)) {
            $data['last_update_time'] = date('Y-m-d H:i:s');
            $id                       = end($workUpdateTime)['id'];
            $updateRes                = $this->workUpdateTime->updateWorkUpdateTimeById((int) $id, $data);
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '更新企业日数据时间失败');
            }
        } else {
            //如果没有新增
            $params = [
                'corp_id'          => $corpId,
                'type'             => Type::CORP_DATA,
                'last_update_time' => date('Y-m-d H:i:s'),
            ];

            $createRes = $this->workUpdateTime->createWorkUpdateTime($params);
            if (! is_int($createRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加企业日数据时间失败');
            }
        }
    }
}
