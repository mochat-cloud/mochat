<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode;

use App\Constants\WorkContactEmployee\Status;
use App\Contract\WorkContactEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 渠道码 - 统计折线图.
 *
 * Class StatisticsLogic
 */
class StatisticsLogic
{
    /**
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $workContactEmployeeService;

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $params): array
    {
        ## 参数校验-当type = 1(按天统计)时间必传
        if ($params['type'] == 1 && (empty($params['startTime']) || empty($params['endTime']))) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '按天统计开始和结束时间必传');
        }
        ## 获取该渠道码邀请的客户列表
        $contactList = $this->getContactList($params);
        ## 根据统计类型组织列表结构
        $formData = $this->formData($params);

        return $this->syncContactList($contactList, $formData, $params);
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getContactList(array $params): array
    {
        $contactList = $this->workContactEmployeeService->getWorkContactEmployeesByState('channelCode-' . $params['channelCodeId'], ['id', 'status', 'create_time', 'deleted_at']);
        return $contactList ?? [];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function formData($params): array
    {
        $data = [];
        if ($params['type'] == 1) { ## 按天统计
            $stime = strtotime($params['startTime']);
            $etime = strtotime($params['endTime']);
            while ($stime <= $etime) {
                $data[] = [
                    'time'             => date('Y-m-d', $stime),
                    'addNumRange'      => 0,
                    'defriendNumRange' => 0,
                    'deleteNumRange'   => 0,
                    'netNumRange'      => 0,
                ];
                $stime = $stime + 86400;
            }
        } elseif ($params['type'] == 2) { ## 按自然周统计
            $beforeWeekDay = date('Y-m-d', strtotime('-1 week'));
            for ($i = 1; $i <= 7; ++$i) {
                $data[] = [
                    'time'             => date('Y-m-d', strtotime('+' . $i . ' days', strtotime($beforeWeekDay))),
                    'addNumRange'      => 0,
                    'defriendNumRange' => 0,
                    'deleteNumRange'   => 0,
                    'netNumRange'      => 0,
                ];
            }
        } else { ## 按自然年统计
            $beforeYearMonth = date('Y-m', strtotime('-1 year'));
            for ($i = 1; $i <= 12; ++$i) {
                $data[] = [
                    'time'             => date('Y-m', strtotime('+' . $i . ' months', strtotime($beforeYearMonth))),
                    'addNumRange'      => 0,
                    'defriendNumRange' => 0,
                    'deleteNumRange'   => 0,
                    'netNumRange'      => 0,
                ];
            }
        }

        return array_column($data, null, 'time');
    }

    /**
     * @param array $contactList 客户数据
     * @param array $formData 响应数据格式
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function syncContactList(array $contactList, array $formData, array $params): array
    {
        ## 组织响应格式
        $data = [
            'addNum'          => 0,
            'defriendNum'     => 0,
            'deleteNum'       => 0,
            'netNum'          => 0,
            'addNumLong'      => 0,
            'defriendNumLong' => 0,
            'deleteNumLong'   => 0,
            'netNumLong'      => 0,
            'list'            => array_values($formData),
        ];
        if (empty($contactList)) {
            return $data;
        }
        ## 归纳数据
        $currentDay = date('Y-m-d');
        foreach ($contactList as $v) {
            if (in_array($params['type'], [1, 2])) { ## 按天统计||按自然周统计
                $inKey  = date('Y-m-d', strtotime($v['createTime']));
                $outKey = empty($v['deletedAt']) ? 'outTime' : date('Y-m-d', strtotime($v['deletedAt']));
            } else { ## 按自然年统计
                $inKey  = date('Y-m', strtotime($v['createTime']));
                $outKey = empty($v['deletedAt']) ? 'outTime' : date('Y-m', strtotime($v['deletedAt']));
            }
            date('Y-m-d', strtotime($v['createTime'])) == $currentDay && ++$data['addNum'];
            if (isset($formData[$inKey])) {
                ++$data['addNumLong'];
                ++$formData[$inKey]['addNumRange'];
            }
            ## 客户状态
            if ($v['status'] == Status::REMOVE) {
                date('Y-m-d', strtotime($v['deletedAt'])) == $currentDay && ++$data['deleteNum'];
                if (isset($formData[$outKey])) {
                    ++$data['deleteNumLong'];
                    ++$formData[$outKey]['deleteNumRange'];
                }
            } elseif ($v['status'] == Status::PASSIVE_REMOVE) {
                date('Y-m-d', strtotime($v['deletedAt'])) == $currentDay && ++$data['defriendNum'];
                if (isset($formData[$outKey])) {
                    ++$data['defriendNumLong'];
                    ++$formData[$outKey]['defriendNumRange'];
                }
            }
            ## 净增客户处理
            $data['netNum']     = $data['addNum'] - $data['defriendNum'];
            $data['netNumLong'] = $data['addNumLong'] - $data['defriendNumLong'];
            foreach ($formData as &$form) {
                $form['netNumRange'] = $form['addNumRange'] - $form['defriendNumRange'];
            }
            $data['list'] = array_values($formData);
        }
        return $data;
    }
}
