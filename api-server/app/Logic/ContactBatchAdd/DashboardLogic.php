<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ContactBatchAdd;

use App\Contract\ContactBatchAddAllotServiceInterface;
use App\Contract\ContactBatchAddImportServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * 导入客户-统计数据.
 *
 * Class Dashboard
 */
class DashboardLogic
{
    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ContactBatchAddImportServiceInterface
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var ContactBatchAddAllotServiceInterface
     */
    protected $contactBatchAddAllotService;

    public function handle(array $params): array
    {
        $corpId = $params['corp_id'];

        return $this->handleContact($corpId);
    }

    /**
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function handleContact(int $corpId): array
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
}
