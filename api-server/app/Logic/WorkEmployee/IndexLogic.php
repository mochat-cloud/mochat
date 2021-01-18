<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkEmployee;

use App\Constants\WorkEmployee\ContactAuth;
use App\Constants\WorkEmployee\Gender;
use App\Constants\WorkEmployee\Status as EmployeeStatus;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkEmployeeStatisticServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\WeWork\WeWork;

/**
 * 成员管理-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WeWork
     */
    protected $client;

    /**
     * @Inject
     * @var WorkEmployeeStatisticServiceInterface
     */
    protected $workEmployeeStatisticService;

    /**
     * 成员列表.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $params): array
    {
        // 组织响应数据
        $data = [
            'page' => [
                'page'      => $params['page'],
                'perPage'   => $params['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        // 查询成员基本信息
        $employeeData = $this->getEmployee($params);
        if (empty($employeeData['data'])) {
            return $data;
        }
        $data['page'] = [
            'page'      => $params['page'],
            'perPage'   => $params['perPage'],
            'total'     => ! empty($employeeData['total']) ? $employeeData['total'] : 0,
            'totalPage' => ! empty($employeeData['last_page']) ? $employeeData['last_page'] : 0,
        ];
        foreach ($employeeData['data'] as $key => $value) {
            $employeeIds[] = $value['id'];
        }
        // 客户统计数据
        $employeeStatistics = $this->getEmployeeStatistics($employeeIds);
        foreach ($employeeData['data'] as $key => $employee) {
            // 成员总聊天数量
            $employeeData['data'][$key]['messageNums'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['chatCnt'])) {
                $employeeData['data'][$key]['messageNums'] = $employeeStatistics[$employee['id']]['chatCnt'];
            }
            // 成员发送消息数量
            $employeeData['data'][$key]['sendMessageNums'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['messageCnt'])) {
                $employeeData['data'][$key]['sendMessageNums'] = $employeeStatistics[$employee['id']]['messageCnt'];
            }
            // 已回复聊天占比
            $employeeData['data'][$key]['replyMessageRatio'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['replyPercentage'])) {
                $employeeData['data'][$key]['replyMessageRatio'] = bcdiv($employeeStatistics[$employee['id']]['replyPercentage'], 100, 2);
            }
            // 新增客户数
            $employeeData['data'][$key]['addNums'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['newContactCnt'])) {
                $employeeData['data'][$key]['addNums'] = $employeeStatistics[$employee['id']]['newContactCnt'];
            }
            // 发起申请数
            $employeeData['data'][$key]['applyNums'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['newApplyCnt'])) {
                $employeeData['data'][$key]['applyNums'] = $employeeStatistics[$employee['id']]['newApplyCnt'];
            }
            // 删除/拉黑客户数
            $employeeData['data'][$key]['invalidContact'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['negativeFeedbackCnt'])) {
                $employeeData['data'][$key]['invalidContact'] = $employeeStatistics[$employee['id']]['negativeFeedbackCnt'];
            }
            // 平均首次回复时长
            $employeeData['data'][$key]['averageReply'] = 0;
            if (! empty($employeeStatistics[$employee['id']]['avgReplyTime'])) {
                $employeeData['data'][$key]['averageReply'] = $employeeStatistics[$employee['id']]['avgReplyTime'];
            }
            // 状态
            $employeeData['data'][$key]['statusName'] = EmployeeStatus::getMessage($employee['status']);
            // 性别
            $employeeData['data'][$key]['gender'] = Gender::getMessage($employee['gender']);
            // 头像
            $employeeData['data'][$key]['thumbAvatar'] = $employee['thumbAvatar'] ? file_full_url($employee['thumbAvatar']) : '';
            // 外部联系人
            $employeeData['data'][$key]['contactAuthName'] = ContactAuth::getMessage($employee['contactAuth']);
        }
        $data['list'] = $employeeData['data'];
        return $data;
    }

    /**
     * 成员基础信息.
     */
    protected function getEmployee(array $params): array
    {
        // 搜索条件
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'updated_at desc',
        ];
        //判断权限是否为全公司
        if (empty($params['user']['dataPermission'])) {
            if (is_array($params['corpId'])) {
                $where[] = ['corp_id', 'IN', $params['corpId']];
            } else {
                $where[] = ['corp_id', '=', $params['corpId']];
            }
        } else {
            $where[] = ['id', 'IN',  $params['user']['deptEmployeeIds']];
        }
        if (! empty($params['name'])) {
            $where[] = ['name', 'LIKE', '%' . $params['name'] . '%'];
        }
        if (! empty($params['status'])) {
            $where[] = ['status', '=', $params['status']];
        }
        if ($params['contactAuth'] != 'all') {
            $where[] = ['contact_auth', '=', $params['contactAuth']];
        }
        // 员工基础信息
        return $this->workEmployeeService->getWorkEmployeeList(
            $where,
            ['id', 'name', 'thumb_avatar', 'status', 'contact_auth', 'wx_user_id', 'corp_id', 'gender'],
            $options
        );
    }

    /**
     * 成员统计
     */
    protected function getEmployeeStatistics(array $employeeIds): array
    {
        $employeeStatisticsIds = $this->workEmployeeStatisticService->getEmployeeStatisticIdsByEmployeeIds($employeeIds, ['id', 'employee_id'], ['groupBy' => ['employee_id']]);
        if (empty($employeeStatisticsIds)) {
            return [];
        }
        foreach ($employeeStatisticsIds as $esk => $esv) {
            $statisticIds[] = $esv['id'];
        }
        $employeeStatisticsData = $this->workEmployeeStatisticService->getWorkEmployeeStatisticsById($statisticIds);
        if (empty($employeeStatisticsData)) {
            return [];
        }
        $employeeStatistics = [];
        foreach ($employeeStatisticsData as $esk => $esv) {
            $employeeStatistics[$esv['employeeId']] = $esv;
        }
        unset($employeeStatisticsIds, $employeeStatisticsData);
        return $employeeStatistics;
    }
}
