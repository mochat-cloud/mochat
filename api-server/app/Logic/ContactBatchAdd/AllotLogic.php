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
use App\Contract\ContactBatchAddAllotServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 导入客户-勾选客户分配员工.
 *
 * Class AllotLogic
 */
class AllotLogic
{
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

        ## $group['1']; ## 先回收再分配
        $recycleArr = $group['1']->toArray();
        $allotrRecycle = [];
        foreach ($recycleArr as $item) {
            $allotrRecycle[] = [
                'import_id' => $item['id'],
                'employee_id' => $item['employeeId'],
                'type' => 0,
                'operate_id' => $user['id'],
                'created_at' => date("Y-m-d H:i:s")
            ];
        }


        ## $group['0']; ## 直接分配
        $allotArr = array_merge($group['0']->toArray(), $group['1']->toArray());
        $allotr = [];
        foreach ($allotArr as $item) {
            $allotr[] = [
                'import_id' => $item['id'],
                'employee_id' => $employeeId,
                'type' => 1,
                'operate_id' => $user['id'],
                'created_at' => date("Y-m-d H:i:s")
            ];
        }
        return $allotr;

        ## $group['2']; ## 拒绝操作
        ## $group['3']; ## 拒绝操作

        // TODO 提交回收分配记录
        // TODO 实际分配用户 并且分配次数自增+1

        return [];
    }
}
