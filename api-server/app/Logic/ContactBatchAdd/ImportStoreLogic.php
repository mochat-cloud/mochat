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

use App\Contract\ContactBatchAddImportRecordServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 导入客户-导入提交.
 *
 * Class ImportStoreLogic
 */
class ImportStoreLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportRecordServiceInterface
     */
    protected $contactBatchAddImportRecordService;

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
            ## 创建导入记录
            $record = [
                'corp_id'        => $user['corpIds'][0],
                'title'          => $params['title'],
                'upload_at'      => date('Y-m-d H:i:s'),
                'allot_employee' => json_encode($params['allotEmployee']),
                'tags'           => json_encode($params['tags']),
                'import_num'     => count($contact),
                'file_name'      => $params['fileName'],
                'file_url'       => $params['fileUrl'],
                'created_at'     => date('Y-m-d H:i:s'),
            ];
            $recordId = $this->contactBatchAddImportRecordService->createContactBatchAddImportRecord($record);

            $record['id'] = $recordId;

            ## 处理数据 分配员工
            $contactArr = $this->handleContact($record, $contact);
            ## 数据入表
            ## 包括日志
            $this->insertData($contactArr, $user);
            DB::commit();
            return [
                'number' => count($contactArr),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '导入失败');
        }
    }

    /**
     * @param array $record 导入记录
     * @param array $contact 客户数据
     * @return array 响应数组
     */
    private function handleContact(array $record, array $contact): array
    {
        $contactArr    = []; // 客户数据
        $allotEmployee = json_decode($record['allot_employee'], true);
        $employeeNum   = count($allotEmployee); ##分配员工总数
        foreach ($contact as $key => $item) {
            $employeeId   = $allotEmployee[$key % $employeeNum];
            $contactArr[] = [
                'record_id'   => $record['id'],
                'phone'       => $item[0],
                'upload_at'   => date('Y-m-d H:i:s'),
                'status'      => 1,
                'add_at'      => date('Y-m-d H:i:s'),
                'employee_id' => $employeeId,
                'allot_num'   => 1,
                'remark'      => $item[1],
                'tags'        => $record['tags'],
                'created_at'  => date('Y-m-d H:i:s'),
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
        $lastId   = $this->contactBatchAddImportService->getLastId();
        $idIndex  = $lastId - count($contactArr);
        $allotArr = [];
        foreach ($contactArr as $item) {
            $allotArr[] = [
                'import_id'   => ++$idIndex,
                'employee_id' => $item['employee_id'],
                'type'        => '1',
                'operate_id'  => $user['id'],
                'created_at'  => date('Y-m-d H:i:s'),
            ];
        }
        $this->contactBatchAddAllotService->createContactBatchAddAllots($allotArr);
    }
}
