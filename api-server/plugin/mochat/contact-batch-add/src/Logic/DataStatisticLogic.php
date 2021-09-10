<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Logic;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddAllotContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-统计数据.
 *
 * Class Dashboard
 */
class DataStatisticLogic
{
    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var ContactBatchAddAllotContract
     */
    protected $contactBatchAddAllotService;

    public function handle(array $params): array
    {
        $corpId = $params['corpId'];

        return [
            'employees' => $this->handleEmployees($params),
            'dataStatistic' => $this->getDataStatistic($corpId),
        ];
    }

    /**
     * @param int $params 企业ID
     * @return array 响应数组
     */
    private function handleEmployees(array $params): array
    {
        $employee = $this->workEmployeeService->getWorkEmployeeList(['corp_id' => $params['corpId']], ['id', 'name'], ['orderByRaw' => 'id desc']);
        $co = collect($employee['data']);
        $employeeIds = empty($params['employeeId']) ? $co->pluck('id')->toArray() : $params['employeeId'];
        $data = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
            ['employee_id', 'in', $employeeIds],
        ], ['employee_id', 'status'], [
            'employee_id', 'status', Db::raw('count(1) as num'),
        ]);
        if (! empty($params['startTime'])) {
            $data = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
                ['employee_id', 'in', $employeeIds], ['created_at', '>', $params['startTime']], ['created_at', '<', $params['endTime']],
            ], ['employee_id', 'status'], [
                'employee_id', 'status', Db::raw('count(1) as num'),
            ]);
        }

        $coData = collect($data);

        $allot = $this->contactBatchAddAllotService->getContactBatchAddAllotOptionWhereGroup([
            ['employee_id', 'in', $employeeIds],
            ['type', '=', 0],
        ], ['employee_id'], [
            'employee_id', Db::raw('count(1) as num'),
        ]);
        $coAllot = collect($allot);

        foreach ($employee['data'] as $k => &$item) {
            ## 分配客户数
            $item['allotNum'] = $coData->where('employeeId', $item['id'])->sum('num');
            if ($item['allotNum'] === 0) {
                unset($employee['data'][$k]);
                continue;
            }
            ## 待添加客户数
            $item['toAddNum'] = $coData->where('employeeId', $item['id'])->where('status', 1)->sum('num');
            ## 待通过客户数
            $item['pendingNum'] = $coData->where('employeeId', $item['id'])->where('status', 2)->sum('num');
            ## 已添加客户数
            $item['passedNum'] = $coData->where('employeeId', $item['id'])->where('status', 3)->sum('num');
            ## 回收客户数
            $item['recycleNum'] = $coAllot->where('employeeId', $item['id'])->sum('num');
            ## 添加完成率
            $item['completion'] = intval($item['passedNum'] / ($item['allotNum'] ?: 1) * 10000) / 100;
        }
        unset($item);
        $employee['data'] = array_merge($employee['data']);
        return $employee;
    }

    private function getDataStatistic($corpId)
    {
        ## 导入总客户数
        $result['contactNum'] = $this->contactBatchAddImportService->countContactBatchAddImportByStatus($corpId, 4);
        ## 待分配客户数
        $result['pendingNum'] = $this->contactBatchAddImportService->countContactBatchAddImportByStatus($corpId, 0);
        ## 待添加客户数
        $result['toAddNum'] = $this->contactBatchAddImportService->countContactBatchAddImportByStatus($corpId, 1);
        ## 待通过客户数
        $result['toPendingNum'] = $this->contactBatchAddImportService->countContactBatchAddImportByStatus($corpId, 2);
        ## 已添加客户数
        $result['passedNum'] = $this->contactBatchAddImportService->countContactBatchAddImportByStatus($corpId, 3);
        ## 完成率
        $result['completion'] = intval($result['passedNum'] / ($result['contactNum'] ?: 1) * 10000) / 100;
        return $result;
    }
}
