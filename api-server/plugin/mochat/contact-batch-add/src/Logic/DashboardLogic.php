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
class DashboardLogic
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
        $corpId = $params['corp_id'];

        return [
            'employees' => $this->handleEmployees($corpId),
            'dashboard' => $this->getDashboard($corpId),
        ];
    }

    /**
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function handleEmployees(int $corpId): array
    {
        $employee    = $this->workEmployeeService->getWorkEmployeeList(['corp_id' => $corpId], ['id', 'name'], ['orderByRaw' => 'id desc']);
        $co          = collect($employee['data']);
        $employeeIds = $co->pluck('id')->toArray();

        $data = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
            ['employee_id', 'in', $employeeIds],
        ], ['employee_id', 'status'], [
            'employee_id', 'status', Db::raw('count(1) as num'),
        ]);
        $coData = collect($data);

        $allot = $this->contactBatchAddAllotService->getContactBatchAddAllotOptionWhereGroup([
            ['employee_id', 'in', $employeeIds],
            ['type', '=', 0],
        ], ['employee_id'], [
            'employee_id', Db::raw('count(1) as num'),
        ]);
        $coAllot = collect($allot);

        foreach ($employee['data'] as &$item) {
            ## 分配客户数
            $item['allotNum'] = $coData->where('employeeId', $item['id'])->sum('num');
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
        return $employee;
    }

    private function getDashboard($corpId)
    {
        $data = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
            ['corp_id', '=', $corpId],
        ], ['employee_id', 'status'], [
            'employee_id', 'status', Db::raw('count(1) as num'),
        ]);
        $coData = collect($data);

        ## 导入总客户数
        $result['contactNum'] = $coData->count();
        ## 待分配客户数
        $result['pendingNum'] = $coData->where('status', '=', 0)->count();
        ## 待添加客户数
        $result['toAddNum'] = $coData->where('status', '=', 1)->count();
        ## 待通过客户数
        $result['pendingNum'] = $coData->where('status', '=', 2)->count();
        ## 已添加客户数
        $result['passedNum'] = $coData->where('status', '=', 3)->count();
        ## 完成率
        $result['completion'] = intval($result['passedNum'] / ($result['contactNum'] ?: 1) * 10000) / 100;
        return $result;
    }
}
