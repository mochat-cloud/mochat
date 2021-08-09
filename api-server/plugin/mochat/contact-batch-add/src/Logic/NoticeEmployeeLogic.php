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
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-未添加客户通知员工.
 *
 * Class NoticeEmployeeLogic
 */
class NoticeEmployeeLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @param array $params 请求参数
     */
    public function handle(array $params): array
    {
        ## 获取未分配客户
        $result = $this->handleContact($params);
        if (count($result)) {
            $this->handleNotice($result);
        }
        return [];
    }

    /**
     * @param int $params 参数
     */
    private function handleContact(array $params): array
    {
        $corpId = $params['corpId'];

        return $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereGroup([
            ['corp_id', '=', $corpId],
            ['status', '=', 1],
            ['upload_at', '<=', date('Y-m-d 23:59:59', time() - $params['undoneTimeOut'] * 86400)],
        ], ['employee_id'], [
            'employee_id', Db::raw('count(1) as num'),
        ]);
    }

    private function handleNotice(array $group): array
    {
        // TODO 通知
        foreach ($group as $item) {
            var_dump([
                $item['employeeId'], ## 员工ID
                $item['num'], ## 未添加数量
            ]);
        }
        return [];
    }
}
