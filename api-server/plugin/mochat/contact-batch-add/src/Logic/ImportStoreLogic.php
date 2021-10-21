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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddAllotContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportRecordContract;

/**
 * 导入客户-导入提交.
 *
 * Class ImportStoreLogic
 */
class ImportStoreLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportRecordContract
     */
    protected $contactBatchAddImportRecordService;

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
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployee;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, array $contact, array $user): array
    {
        DB::beginTransaction();
        try {
            $corpId = $user['corpIds'][0];
            foreach ($contact as $k => $item) {
                $info = $this->contactBatchAddImportService->getContactBatchAddImportByPhone($corpId, (string) $item[0]);
                if (! empty($info)) {
                    unset($contact[$k]);
                }
            }
            if (empty($contact)) {
                return ['number' => 0];
            }
            $contact = array_merge($contact);
            ## 创建导入记录
            $record = [
                'corp_id' => $corpId,
                'title' => $params['title'],
                'upload_at' => date('Y-m-d H:i:s'),
                'allot_employee' => json_encode($params['allotEmployee']),
                'tags' => json_encode($params['tags']),
                'import_num' => count($contact),
                'file_name' => $params['fileName'],
                'file_url' => $params['fileUrl'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $recordId = $this->contactBatchAddImportRecordService->createContactBatchAddImportRecord($record);
            $record['id'] = $recordId;
            ## 处理数据 分配员工
            $contactArr = $this->handleContact($record, $contact, $corpId);
            ## 数据入表 包括日志
            $this->insertData($contactArr, $user);
            $this->sendMessage($recordId, $corpId);
            DB::commit();
            return [
                'number' => count($contactArr),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '导入失败' . $e->getMessage());
        }
    }

    /**
     * @param array $record 导入记录
     * @param array $contact 客户数据
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function handleContact(array $record, array $contact, int $corpId): array
    {
        $contactArr = []; // 客户数据
        $allotEmployee = json_decode($record['allot_employee'], true);
        $employeeNum = count($allotEmployee); ##分配员工总数
        foreach ($contact as $key => $item) {
            $employeeId = $allotEmployee[$key % $employeeNum];
            $contactArr[] = [
                'corp_id' => $corpId,
                'record_id' => $record['id'],
                'phone' => $item[0],
                'upload_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'add_at' => date('Y-m-d H:i:s'),
                'employee_id' => $employeeId,
                'allot_num' => 1,
                'remark' => $item[1],
                'tags' => $record['tags'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        return $contactArr;
    }

    /**
     * @param array $params 客户数据
     */
    private function insertData(array $contactArr, array $user)
    {
        ## 导入客户
        $this->contactBatchAddImportService->createContactBatchAddImports($contactArr);
        // TODO 这种获取方式需要优化
        $lastId = $this->contactBatchAddImportService->getLastId();
        $idIndex = $lastId - count($contactArr);
        $allotArr = [];
        foreach ($contactArr as $item) {
            $allotArr[] = [
                'import_id' => ++$idIndex,
                'employee_id' => $item['employee_id'],
                'type' => 1,
                'operate_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->contactBatchAddAllotService->createContactBatchAddAllots($allotArr);
    }

    private function sendMessage(int $recordId, int $corpId)
    {
        $list = $this->contactBatchAddImportService->countContactBatchAddImportByRecordId($recordId, ['employee_id', Db::raw('COUNT(0) AS `count`')]);
        $messageRemind = make(MessageRemind::class);
        $workAgentService = make(WorkAgentContract::class);
        $agent = $workAgentService->getWorkAgentRemindByCorpId((int) $corpId, ['id']);

        if (empty($agent)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '未配置自建应用信息，无法发送提醒！');
        }

        foreach ($list as $item) {
            $employee = $this->workEmployee->getWorkEmployeeById($item['employeeId']);
            $url = Url::getSidebarBaseUrl() . '/contactBatchAdd?agentId=' . $agent['id'] . '&batchId=' . $recordId;
            $text = "【管理员提醒】您有客户未添加哦！\n" .
                "提醒事项：添加客户\n" .
                "客户数量：{$item['count']}名\n" .
                "记得及时添加哦\n" .
                "<a href='{$url}'>点击查看详情</a>";
            $messageRemind->sendToEmployee(
                (int) $employee['corpId'],
                $employee['wxUserId'],
                'text',
                $text
            );
        }
    }
}
