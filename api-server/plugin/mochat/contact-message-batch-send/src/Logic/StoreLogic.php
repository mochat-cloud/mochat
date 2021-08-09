<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;
use MoChat\Plugin\ContactMessageBatchSend\QueueService\StoreApply;

class StoreLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @Inject
     * @var ContactMessageBatchSendResultContract
     */
    private $contactMessageBatchSendResult;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployee;

    /**
     * 客户.
     * @Inject
     * @var WorkContactContract
     */
    private $workContact;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    private $workContactEmployee;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    private $workRoom;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    private $workContactRoom;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTag;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @param array $user 当前用户信息
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

        if (! empty($filterParams['addTimeStart'])) {
            $filterParamsDetail['addTimeStart'] = $filterParams['addTimeStart'];
        }

        if (isset($filterParams['addTimeEnd'])) {
            $filterParamsDetail['addTimeEnd'] = $filterParams['addTimeEnd'];
        }

        if (! empty($filterParams['rooms'])) {
            $filterParamsDetail['rooms'] = $this->workRoom->getWorkRoomsById($filterParams['rooms'], ['id', 'name']);
        }
        if (! empty($filterParams['tags'])) {
            $filterParamsDetail['tags'] = $this->workContactTag->getWorkContactTagsById($filterParams['tags'], ['id', 'name']);
        }
        if (! empty($filterParams['excludeContacts'])) {
            $filterParamsDetail['excludeContacts'] = $this->workContact->getWorkContactsById($filterParams['excludeContacts'], ['id', 'name']);
        }

        ## 入库
        Db::beginTransaction();
        try {
            $batchContent = $params['content'];
            $batchId      = $this->contactMessageBatchSend->createContactMessageBatchSend([
                'corp_id'              => $corpId,
                'user_id'              => $user['id'],
                'user_name'            => $user['name'] ?: $user['phone'],
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
                ++$employeeTotal;
                ## 获取成员客户
                $contacts = $this->getWorkContactsByEmployeeFilterParams($employee['id'], $filterParams);
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
                        ++$contactTotal;
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

    /**
     * 获取过滤后的多条
     * @param array $params 过滤参数
     * @return array 响应数组
     */
    protected function getWorkContactsByEmployeeFilterParams(int $employeeId, array $params)
    {
        $gender          = $params['gender'] ?? null;
        $rooms           = $params['rooms'] ?? [];
        $addTimeStart    = $params['addTimeStart'] ?? null;
        $addTimeEnd      = $params['addTimeEnd'] ?? null;
        $tags            = $params['tags'] ?? [];
        $excludeContacts = $params['excludeContacts'] ?? [];

        ## 查询条件
        $where = [];
        if ($gender !== null) {
            $where[] = ['gender', '=', $gender];
        }

        ## 获取成员所有客户
        $contactIds = $this->workContactEmployee->getWorkContactEmployeeContactIdsByEmployeeId($employeeId, $addTimeStart, $addTimeEnd);

        if (! empty($excludeContacts)) {
            $contactIds = array_diff($contactIds, (array) $excludeContacts);
        }

        if (! empty($rooms)) {
            $roomContactIds = $this->workContactRoom->getWorkContactRoomsContactIdsByRoomIds((array) $rooms);
            $contactIds     = array_intersect($contactIds, $roomContactIds);
        }

        return $this->workContact->getWorkContactsByIdsTagIds($contactIds, (array) $tags);
    }
}
