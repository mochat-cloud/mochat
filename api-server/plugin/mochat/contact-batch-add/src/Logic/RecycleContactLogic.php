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

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddAllotContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-超时回收客户.
 *
 * Class RecycleContactLogic
 */
class RecycleContactLogic
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
     * @Inject
     * @var ContactBatchAddAllotContract
     */
    protected $contactBatchAddAllotService;

    /**
     * @param array $params 请求参数
     */
    public function handle(array $params): array
    {
        ## 获取超时客户
        $result = $this->handleContact($params);
        if (count($result)) {
            $this->handleRecycle($result);
        }
        return [];
    }

    /**
     * @param int $params 参数
     */
    private function handleContact(array $params): array
    {
        $corpId = $params['corpId'];

        return $this->contactBatchAddImportService->getContactBatchAddImportOptionWhere([
            ['corp_id', '=', $corpId],
            ['status', '=', 2],
            ['updated_at', '<=', date('Y-m-d H:i:s', time() - $params['recycleTimeOut'] * 86400)],
        ], [], ['id', 'employee_id']);
    }

    private function handleRecycle(array $contact): array
    {
        ## 回收记录
        $co = collect($contact);
        $allotRecycle = []; ## 回收分配记录
        $updateContact = []; ## 客户回收修改数据
        foreach ($contact as $item) {
            $allotRecycle[] = [
                'import_id' => $item['id'],
                'employee_id' => $item['employeeId'],
                'type' => 0,
                'operate_id' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $updateContact[] = [
                'id' => $item['id'],
                'status' => 0,
                'employee_id' => 0,
            ];
        }
        $this->contactBatchAddAllotService->createContactBatchAddAllots($allotRecycle);

        $updateNum = $this->contactBatchAddImportService->updateContactBatchAddImports($updateContact);

        ## 状态改为回收
        return [
            'updateNum' => $updateNum, ## 回收数量
        ];
    }
}
