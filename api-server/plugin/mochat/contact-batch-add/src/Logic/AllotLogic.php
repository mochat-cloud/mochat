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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddAllotContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-勾选客户分配员工.
 *
 * Class AllotLogic
 */
class AllotLogic
{
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

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, $user): array
    {
        DB::beginTransaction();
        try {
            $res = $this->handleContact($params, $user);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '指派失败');
        }

        return $res;
    }

    /**
     * @param array $params 参数
     * @return array 响应数组
     */
    private function handleContact(array $params, array $user): array
    {
        $ids = $params['id'];
        $employeeId = $params['employeeId'];
        $contact = $this->contactBatchAddImportService->getContactBatchAddImportsById($ids, ['id', 'employee_id', 'status']);
        $co = collect($contact);
        $group = $co->groupBy('status');

        ## $group['1']; ## 已分配 先回收再分配
        $recycleArr = $group->get('1', collect([]))->toArray();
        $allotRecycle = []; ## 回收分配记录
        foreach ($recycleArr as $item) {
            $allotRecycle[] = [
                'import_id' => $item['id'],
                'employee_id' => $item['employeeId'],
                'type' => 0,
                'operate_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        ## $group['0']; ## 未分配 直接分配
        $allotArr = array_merge($group->get('0', collect([]))->toArray(), $group->get('1', collect([]))->toArray()); ## 重新分配叠加之前已回收的

        $allot = []; ## 分配记录
        $updateContact = []; ## 客户重新分配员工数据

        foreach ($allotArr as $item) {
            $info = $this->contactBatchAddImportService->getContactBatchAddImportById($item['id'], ['employee_id', 'allot_num']);
            $allot[] = [
                'import_id' => $item['id'],
                'employee_id' => $employeeId,
                'type' => 1,
                'operate_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $allotNum = $info['employeeId'] === $employeeId ? $info['allotNum'] : $info['allotNum'] + 1;
            $updateContact[] = [
                'id' => $item['id'],
                'status' => 1,
                'employee_id' => $employeeId,
                'allot_num' => $allotNum, //DB::raw('allot_num+ 1'),
            ];
        }
        ## $group['2']; ## 申请中拒绝操作
        ## $group['3']; ## 已添加拒绝操作

        ## 提交回收分配记录
        $allotMerge = array_merge($allotRecycle, $allot);
        $this->contactBatchAddAllotService->createContactBatchAddAllots($allotMerge);

        ## 实际分配用户 并且分配次数自增+1
        $updateNum = $this->contactBatchAddImportService->updateContactBatchAddImports($updateContact);

        return [
            'updateNum' => $updateNum,
        ];
    }
}
