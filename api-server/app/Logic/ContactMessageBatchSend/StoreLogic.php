<?php

declare(strict_types=1);

/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Logic\ContactMessageBatchSend;


use App\Contract\ContactMessageBatchSendEmployeeServiceInterface;
use App\Contract\ContactMessageBatchSendResultServiceInterface;
use App\Contract\ContactMessageBatchSendServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\QueueService\ContactMessageBatchSend\StoreApply;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class StoreLogic
{

    /**
     * @Inject()
     * @var ContactMessageBatchSendServiceInterface
     */
    private $contactMessageBatchSend;

    /**
     * @Inject()
     * @var ContactMessageBatchSendEmployeeServiceInterface
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @Inject()
     * @var ContactMessageBatchSendResultServiceInterface
     */
    private $contactMessageBatchSendResult;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $workEmployee;

    /**
     * 客户.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $workContact;

    /**
     * @Inject()
     * @var WorkRoomServiceInterface
     */
    private $workRoom;

    /**
     * @Inject()
     * @var WorkContactTagServiceInterface
     */
    private $workContactTag;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param  array  $params
     * @param  array  $user
     * @return array
     */
    public function handle(array $params, array $user): bool
    {
        $corpId      = $user['corpIds'][0];
        $employeeIds = (array) $params['employeeIds'];

        ## 获取用户成员
        $employees = $this->workEmployee->getWorkEmployeesByIdCorpIdStatus($corpId, $employeeIds, 1, ['id', 'wx_user_id']);
        if (count($employees) !== count($employeeIds)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS);
        }

        $filterParams = empty($params['filterParams']) ? [] : (array) $params['filterParams'];

        $filterParamsDetail = [
            'gender'          => $filterParams['gender'] ?? '',
            'addTimeStart'    => $filterParams['addTimeStart'] ?? '',
            'addTimeEnd'      => $filterParams['addTimeEnd'] ?? '',
            'rooms'           => [],
            'tags'            => [],
            'excludeContacts' => [],
        ];

        if (isset($filterParams['gender'])) {
            $filterParamsDetail['gender'] = $filterParams['gender'];
        }

        if (!empty($filterParams['addTimeStart'])) {
            $filterParamsDetail['addTimeStart'] = $filterParams['addTimeStart'];
        }

        if (isset($filterParams['addTimeEnd'])) {
            $filterParamsDetail['addTimeEnd'] = $filterParams['addTimeEnd'];
        }

        if (!empty($filterParams['rooms'])) {
            $filterParamsDetail['rooms'] = $this->workRoom->getWorkRoomsById($filterParams['rooms'], ['id', 'name']);
        }
        if (!empty($filterParams['tags'])) {
            $filterParamsDetail['tags'] = $this->workContactTag->getWorkContactTagsById($filterParams['tags'], ['id', 'name']);
        }
        if (!empty($filterParams['excludeContacts'])) {
            $filterParamsDetail['excludeContacts'] = $this->workContact->getWorkContactsById($filterParams['excludeContacts'], ['id', 'name']);
        }

        ## 入库
        Db::beginTransaction();
        try {
            $batchContent = $params['content'];
            $batchId      = $this->contactMessageBatchSend->createContactMessageBatchSend([
                'corp_id'              => $corpId,
                'user_id'              => $user['id'],
                'user_name'            => $user['name'],
                'filter_params'        => json_encode($filterParams, JSON_UNESCAPED_UNICODE),
                'filter_params_detail' => json_encode($filterParamsDetail, JSON_UNESCAPED_UNICODE),
                'content'              => json_encode($batchContent, JSON_UNESCAPED_UNICODE),
                'send_way'             => $params['sendWay'],
                'definite_time'        => $params['definiteTime'],
                'created_at'           => date('Y-m-d H:i:s'),
            ]);

            $employeeTotal = 0;
            $contactTotal  = 0;
            foreach ($employees as $employee) {
                $employeeTotal++;
                ## 获取成员客户
                $contacts     = $this->workContact->getWorkContactsByEmployeeFilterParams($employee['id'], $filterParams);
                $contactTotal += count($contacts);
                ## 扩展多条消息
                foreach ($batchContent as $content) {
                    ## 客户
                    $contactTotal = 0;
                    foreach ($contacts as $contact) {
                        $this->contactMessageBatchSendResult->createContactMessageBatchSendResult([
                            'batch_id'         => $batchId,
                            'employee_id'      => $employee['id'],
                            'contact_id'       => $contact['id'],
                            'external_user_id' => $contact['wxExternalUserid'],
                            'created_at'       => date('Y-m-d H:i:s'),
                        ]);
                        $contactTotal++;
                    }
                    ## 成员
                    $this->contactMessageBatchSendEmployee->createContactMessageBatchSendEmployee([
                        'batch_id'           => $batchId,
                        'employee_id'        => $employee['id'],
                        'wx_user_id'         => $employee['wxUserId'],
                        'send_contact_total' => $contactTotal,
                        'content'            => json_encode($content, JSON_UNESCAPED_UNICODE),
                        'created_at'         => date('Y-m-d H:i:s'),
                        'last_sync_time'     => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            $this->contactMessageBatchSend->updateContactMessageBatchSendById($batchId, [
                'sendEmployeeTotal' => $employeeTotal,
                'sendContactTotal'  => $contactTotal,
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户消息创建失败');
        }

        if ($params['sendWay'] == 1) {
            make(StoreApply::class)->handle($batchId);
        }

        return true;
    }
}