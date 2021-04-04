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

use App\Contract\ContactBatchAddImportServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * 导入客户-未跟进通知管理员.
 *
 * Class NoticeLeaderLogic
 */
class NoticeLeaderLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportServiceInterface
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @param array $params 请求参数
     */
    public function handle(array $params): array
    {
        ## 获取未完成员工
        $unfinished = $this->handleContact($params);
        $this->handleUnfinished($unfinished);
        return [];
    }

    /**
     * @param int $params 参数
     */
    private function handleContact(array $params): array
    {
        $corpId      = $params['corpId'];
        $employee    = $this->workEmployeeService->getWorkEmployeesByCorpId($corpId, ['id', 'name']);
        $coEmployee  = collect($employee);
        $employeeIds = $coEmployee->pluck('id')->toArray(); ## 公司所有员工ID

        $unfinished = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
            ['employee_id', 'in', $employeeIds],
            ['status', '=', 1],
            ['updated_at', '<=', date('Y-m-d 23:59:59', time() - $params['pendingTimeOut'] * 86400)],
        ], ['employee_id'], [
            'employee_id', Db::raw('count(1) as num'),
        ]);

        $coEmployee = $coEmployee->keyBy('id');
        foreach ($unfinished as &$item) {
            $item['name'] = $coEmployee[$item['employeeId']]['name'];
        }
        unset($item);

        return $unfinished;
    }

    private function handleUnfinished(array $unfinished): array
    {
        // TODO 通知
        foreach ($unfinished as $item) {
            $item['employeeId']; ## 员工ID
            $item['num']; ## 未完成数量
            $item['name']; ## 员工名
        }
        return [];
    }
}
