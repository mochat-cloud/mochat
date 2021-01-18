<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode\Traits;

use App\Constants\ChannelCode\AutoAddFriend;
use App\Constants\ChannelCode\Status;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\QueueService\ChannelCode\QrCodeUpdateApply;

trait ChannelCodeTrait
{
    /**
     * 更新渠道码二维码
     * @param $drainageEmployee
     * @param $autoAddFriend
     * @param $corpId
     * @param $channelCodeId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleQrCode(array $drainageEmployee, int $autoAddFriend, int $corpId, int $channelCodeId, string $wxConfigId = '')
    {
        //处理引流成员设置 获取满足条件的员工id
        $employeeIds = $this->handelEmployee($drainageEmployee);
        if (! empty($employeeIds)) {
            //获取成员微信id
            $employeeWxUserId = $this->getEmployee($employeeIds);
            if (! empty($employeeWxUserId)) {
                ## 生成-配置客户联系「联系我」方式-二维码
                $skipVerify = $autoAddFriend == AutoAddFriend::OPEN ? true : false;
                (new QrCodeUpdateApply())->handle(
                    $corpId,
                    $channelCodeId,
                    $employeeWxUserId,
                    $skipVerify,
                    $wxConfigId
                );
            }
        }
    }

    /**
     * 处理引流成员设置.
     * @param $drainageEmployee
     * @return array
     */
    private function handelEmployee($drainageEmployee)
    {
        $employeeIds = [];
        //先看特殊时期
        if (! empty($drainageEmployee['specialPeriod']['detail'])
            && $drainageEmployee['specialPeriod']['status'] == Status::OPEN) {
            foreach ($drainageEmployee['specialPeriod']['detail'] as &$val) {
                //判断当前日期是否在特殊时期内
                if ($val['startDate'] <= date('Y-m-d') && date('Y-m-d') <= $val['endDate']) {
                    foreach ($val['timeSlot'] as &$v) {
                        //判断当前时间点在哪个时间点范围
                        if ($v['startTime'] <= date('H:i') && date('H:i') <= $v['endTime']) {
                            $employeeIds = $v['employeeId'];
                        } else {
                            //当前时间点不在设置的时间段的用设置的24小时成员
                            if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                                $employeeIds = $v['employeeId'];
                            }
                        }
                    }
                    unset($v);
                }
            }
            unset($val);
        }

        //若当前日期不在特殊时期 查看企业成员设置
        if (empty($employeeIds)) {
            //获取今天是周几 查询今天的24小时企业成员
            $week = date('w', time());  //返回数字
            foreach ($drainageEmployee['employees'] as &$raw) {
                //判断今天是周几 获取周期的时间段
                if ($raw['week'] == $week) {
                    foreach ($raw['timeSlot'] as &$v) {
                        //判断当前时间点在哪个时间点范围
                        if ($v['startTime'] <= date('H:i') && date('H:i') <= $v['endTime']) {
                            $employeeIds = $v['employeeId'];
                        } else {
                            //不在时间段的用24小时成员
                            if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                                $employeeIds = $v['employeeId'];
                            }
                        }
                    }
                    unset($v);
                }
            }
            unset($raw);
        }

        $newEmployeeIds = $employeeIds;

        //判断员工已有客户数是否超过上限 超过按备用员工
        if ($drainageEmployee['addMax']['status'] == Status::OPEN) {
            //查询员工已有客户数
            $contactEmployee = $this->countContact($employeeIds);
            foreach ($drainageEmployee['addMax']['employees'] as &$val) {
                //超过上限的移除
                if (isset($contactEmployee[$val['employeeId']])
                    && $contactEmployee[$val['employeeId']]->total >= $val['max']) {
                    unset($val['employeeId']);
                }
            }
            unset($val);

            //没有超过上限的员工
            $noMaxEmployeeIds = array_column($drainageEmployee['addMax']['employees'], 'employeeId');

            //满足时间点的员工 与 没有超过上限的员工取交集
            $newEmployeeIds = array_intersect($employeeIds, $noMaxEmployeeIds);
//            dd($employeeIds,$noMaxEmployeeIds,$newEmployeeIds);
            //如果客户数都超出上限了 取备用员工
            if (empty($newEmployeeIds)) {
                $newEmployeeIds = $drainageEmployee['addMax']['spareEmployeeIds'];
            }
        }

        return $newEmployeeIds;
    }

    /**
     * 获取成员微信id.
     * @param $employeeIds
     * @return array
     */
    private function getEmployee($employeeIds)
    {
        $workEmployee = make(WorkEmployeeServiceInterface::class);

        $res = $workEmployee->getWorkEmployeesById($employeeIds, ['wx_user_id']);
        if (empty($res)) {
            return [];
        }

        return array_column($res, 'wxUserId');
    }

    /**
     * 获取员工已持有客户数.
     * @param $employeeIds
     * @return array
     */
    private function countContact($employeeIds)
    {
        $contactEmployee = make(WorkContactEmployeeServiceInterface::class);

        $res = $contactEmployee->countWorkContactEmployeesByEmployee($employeeIds);
        if (empty($res)) {
            return [];
        }

        return array_column($res, null, 'employee_id');
    }
}
