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
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportRecordContract;

/**
 * 导入客户-导入记录.
 *
 * Class ImportIndexLogic
 */
class ImportIndexLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportRecordContract
     */
    protected $contactBatchAddImportRecordService;

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
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    public function handle(int $corpId): array
    {
        return $this->handleContact($corpId);
    }

    /**
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function handleContact(int $corpId): array
    {
        $record = $this->contactBatchAddImportRecordService->getContactBatchAddImportRecordList([
            'corp_id' => $corpId,
        ], ['id', 'corp_id', 'title', 'upload_at', 'allot_employee', 'tags', 'import_num', 'add_num', 'file_name', 'file_url'], [
            'orderByRaw' => 'id desc',
        ]);
        $allotEmployee = [];
        $tags = [];
        foreach ($record['data'] as $item) {
            $allotEmployee = array_merge($allotEmployee, $item['allotEmployee'] ?: []);
            $tags = array_merge($tags, $item['tags'] ?: []);
        }

        $employees = $this->workEmployeeService->getWorkEmployeesById($allotEmployee, ['id', 'name']);
        $employees = collect($employees)->keyBy('id')->toArray();
        $tags = $this->workContactTagService->getWorkContactTagsById($tags, ['id', 'name']);
        $tags = collect($tags)->keyBy('id')->toArray();

        foreach ($record['data'] as &$item) {
            $tempEmployee = [];
            foreach ($item['allotEmployee'] as $employee) {
                if (isset($employees[$employee])) {
                    $tempEmployee[] = $employees[$employee];
                }
                $employee = $employees[$employee] ?? [];
            }
            $item['allotEmployee'] = $tempEmployee;

            $tempTag = [];
            foreach ($item['tags'] as $tag) {
                if (isset($tags[$tag])) {
                    $tempTag[] = $tags[$tag];
                }
            }
            $item['tags'] = $tempTag;
        }
        unset($item);

        return $record;
    }
}
