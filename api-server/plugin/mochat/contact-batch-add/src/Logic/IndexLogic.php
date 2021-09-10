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
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportRecordContract;

/**
 * 导入客户-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
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
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     */
    public function handle(array $params): array
    {
        $corpId = (int) $params['corpId'];
        $status = ($params['status'] === null || $params['status'] === '') ? null : (int) $params['status'];
        $searchKey = $params['searchKey'];
        $recordId = (int) $params['recordId'];
        return $this->handleContact(
            $corpId,
            $status,
            $searchKey,
            $recordId
        );
    }

    /**
     * @param int $corpId 企业ID
     * @param int $status 状态
     * @param string $searchKey 搜索关键字
     * @param int $recordId 指定导入批次
     */
    private function handleContact(int $corpId, int $status = null, string $searchKey = null, int $recordId = 0): array
    {
        if ($recordId) {
            $recordIds = [$recordId];
        } else {
            $recordIds = $this->getRecordsByCorpId([$corpId], ['id']); ## 导入记录ID
        }
        $searchPhone = $searchRemark = $searchEmployee = [];
        if ($searchKey) {
            if (strlen($searchKey) >= 4 && preg_match('/^\\d*$/', $searchKey)) { ## 大于4位且是数字 搜索手机号
                $searchPhone = $this->searchPhone($searchKey, $recordIds);
            }
            $searchRemark = $this->searchRemark($searchKey, $recordIds); ## 搜索备注
            $searchEmployee = $this->searchEmployee($searchKey, $corpId); ## 搜索员工

            if (count(array_merge($searchPhone, $searchRemark, $searchEmployee)) == 0) { ## 有检索关键字的情况下 任何相关都搜索不到 直接返回空
                return ['data' => []];
            }
        }
        $where = [];
        $where['record_id'] = $recordIds;

        $boolean = 'and'; ## 该变量是用于避免客户手机号/备注 和员工名一致情况下 导致两则条件都存在情况下搜索冲突
        $tempIds = array_merge($searchPhone, $searchRemark); ## 如果客户手机号 和 备注有相关记录 就增加检索条件
        if (count($tempIds)) {
            $where[] = ['id', 'in', $tempIds, $boolean];
            $boolean = 'or';
        }

        if (count($searchEmployee)) { ## 如果有相关员工情况下 加入员工检索
            $where[] = ['employee_id', 'in', $searchEmployee, $boolean];
            $boolean = 'or';
        }

        if ($status !== null) {
            $where['status'] = $status;
        }

        $contact = $this->contactBatchAddImportService->getContactBatchAddImportList(
            $where,
            ['id', 'record_id', 'phone', 'upload_at', 'status', 'add_at', 'employee_id', 'allot_num', 'remark', 'tags'],
            ['orderByRaw' => 'id desc']
        );

        $employeeId = [];
        $tags = [];
        foreach ($contact['data'] as $item) {
            $employeeId = array_merge($employeeId, [$item['employeeId']] ?: []);
            $tags = array_merge($tags, $item['tags'] ?: []);
        }
        $employees = $this->workEmployeeService->getWorkEmployeesById($employeeId, ['id', 'name']);
        $employees = collect($employees)->keyBy('id')->toArray();
        $tags = $this->workContactTagService->getWorkContactTagsById($tags, ['id', 'name']);
        $tags = collect($tags)->keyBy('id')->toArray();

        foreach ($contact['data'] as &$item) {
            if (isset($employees[$item['employeeId']])) {
                $item['allotEmployee'] = $employees[$item['employeeId']];
            } else {
                $item['allotEmployee'] = [];
            }

            $tempTag = [];
            foreach ($item['tags'] as $tag) {
                if (isset($tags[$tag])) {
                    $tempTag[] = $tags[$tag];
                }
            }
            $item['tags'] = $tempTag;
        }
        unset($item);

        return $contact;
    }

    private function getRecordsByCorpId($corpId): array
    {
        $record = $this->contactBatchAddImportRecordService->getContactBatchAddImportRecordsByCorpId([$corpId], ['id']);
        $co = collect($record);
        $recordIds = $co->pluck('id');
        return $recordIds->toArray();
    }

    private function searchPhone(string $searchKey, array $recordIds): array
    {
        $contact = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhere([
            ['phone', 'like', "%{$searchKey}%"],
            ['record_id', 'in', $recordIds],
        ], [], ['id']);
        $co = collect($contact);
        $import = $co->pluck('id');
        return $import->toArray();
    }

    private function searchRemark(string $searchKey, array $recordIds): array
    {
        $contact = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhere([
            ['remark', 'like', "%{$searchKey}%"],
            ['record_id', 'in', $recordIds],
        ], [], ['id']);
        $co = collect($contact);
        $import = $co->pluck('id');
        return $import->toArray();
    }

    private function searchEmployee(string $searchKey, int $corpId): array
    {
        $employees = $this->workEmployeeService->getWorkEmployeesByCorpIdName($corpId, $searchKey, ['id']);
        $co = collect($employees);
        $import = $co->pluck('id');
        return $import->toArray();
    }
}
