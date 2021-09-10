<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\Plugin\ContactMessageBatchSend\Constants\MessageStatus;
use MoChat\Plugin\ContactMessageBatchSend\Constants\Status;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;

/**
 * 同步群发结果.
 */
class SyncSendResultJob extends Job
{
    /**
     * @var array
     */
    public $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $batchEmployeeId = (int) $this->params['batchEmployeeId'];

        $contactMessageBatchSendEmployeeService = make(ContactMessageBatchSendEmployeeContract::class);
        $contactMessageBatchSendService = make(ContactMessageBatchSendContract::class);
        $logger = make(StdoutLoggerInterface::class);

        Db::beginTransaction();
        try {
            $batchEmployee = $contactMessageBatchSendEmployeeService->getContactMessageBatchSendEmployeeLockById($batchEmployeeId, ['id', 'batch_id', 'status', 'msg_id']);

            if (empty($batchEmployee) || $batchEmployee['status'] === Status::SEND_OK) {
                Db::commit();
                return;
            }
            $batch = $contactMessageBatchSendService->getContactMessageBatchSendLockById(
                $batchEmployee['batchId'],
                ['id', 'corp_id', 'send_employee_total', 'send_contact_total']
            );

            if (empty($batch)) {
                Db::commit();
                return;
            }

            $batchResult = $this->getGroupTask((int) $batch['corpId'], $batchEmployee['msgId']);

            if ($batchResult['errcode'] > 0) {
                // 更新成员状态
                $logger->error(sprintf('%s [%s] %s', '客户群发消息同步结果失败', date('Y-m-d H:i:s'), $batchResult['errmsg']));
                Db::commit();
                return;
            }

            $sendTime = null;

            foreach ($batchResult['task_list'] as $result) {
                if ($result['status'] == MessageStatus::NOT_SEND) {
                    continue;
                }

                $sendStatus = Status::SEND_OK;
                $sendTime = date('Y-m-d H:i:s', $result['send_time']);

                // 更新成员状态
                $contactMessageBatchSendEmployeeService->updateContactMessageBatchSendEmployeeById($batchEmployeeId, [
                    'errCode' => $batchResult['errcode'],
                    'errMsg' => $batchResult['errmsg'],
                    'status' => $sendStatus,
                    'sendTime' => $sendTime,
                ]);

                $this->updateContactMessageSendResult($batch['corpId'], $batchEmployee['batchId'], $batchEmployee['msgId'], $result['userid'], 500, '');
            }

            $this->updateSendTotal($batchEmployee['batchId'], $batch['sendEmployeeTotal'], $batch['sendContactTotal']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $logger->error(sprintf('%s [%s] %s', '客户群发消息同步结果失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $logger->error($e->getTraceAsString());
        }
    }

    /**
     * 获取企业群发成员执行结果.
     *
     * @see https://open.work.weixin.qq.com/api/doc/90000/90135/93338
     *
     * @return array
     */
    public function getGroupSendResult(int $corpId, string $msgId, string $userId, int $limit = 500, string $cursor = '')
    {
        $weWorkFactory = make(WeWorkFactory::class);
        $weWorkContactApp = $weWorkFactory->getContactApp($corpId);
        return $weWorkContactApp->external_contact_message->httpPostJson('cgi-bin/externalcontact/get_groupmsg_send_result', [
            'msgid' => $msgId,
            'userid' => $userId,
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }

    /**
     * 获取客户发送结果.
     */
    private function updateContactMessageSendResult(int $corpId, int $batchId, string $msgId, string $employeeUserId, int $limit = 500, string $cursor = '')
    {
        $logger = make(StdoutLoggerInterface::class);
        $contactMessageBatchSendResultService = make(ContactMessageBatchSendResultContract::class);

        $sendResult = $this->getGroupSendResult($corpId, $msgId, $employeeUserId, $limit, $cursor);

        if ($sendResult['errcode'] !== 0) {
            $logger->error(sprintf('%s [%s] %s', '客户群发消息客户同步结果失败', date('Y-m-d H:i:s'), $sendResult['errmsg']));
            return;
        }

        if (empty($sendResult['send_list'])) {
            return;
        }

        foreach ($sendResult['send_list'] as $result) {
            $row = $contactMessageBatchSendResultService->getContactMessageBatchSendResultByBatchUserId($batchId, $result['external_userid'], ['id']);
            if (! empty($row)) {
                // 同步结果
                $contactMessageBatchSendResultService->updateContactMessageBatchSendResultById($row['id'], [
                    'userId' => $result['userid'],
                    'status' => $result['status'],
                    'sendTime' => $result['send_time'] ?? 0,
                ]);
            }
        }

        if ($sendResult['next_cursor'] === '') {
            return;
        }

        // 获取下一页
        $this->updateContactMessageSendResult($corpId, $batchId, $msgId, $employeeUserId, $limit, $sendResult['next_cursor']);
    }

    private function updateSendTotal(int $batchId, int $sendEmployeeTotal, int $sendContactTotal)
    {
        $contactMessageBatchSendEmployeeService = make(ContactMessageBatchSendEmployeeContract::class);
        $contactMessageBatchSendService = make(ContactMessageBatchSendContract::class);
        $contactMessageBatchSendResultService = make(ContactMessageBatchSendResultContract::class);

        // 获取已发送成员总数
        $sendTotal = $contactMessageBatchSendEmployeeService->getContactMessageBatchSendEmployeeCount([
            ['batch_id', '=', $batchId],
            ['status', '=', Status::SEND_OK],
        ]);

        // 获取已送达客户总数
        $receivedTotal = $contactMessageBatchSendResultService->getContactMessageBatchSendResultCount([
            ['batch_id', '=', $batchId],
            ['status', '=', MessageStatus::SEND_OK],
        ]);
        // 获取客户因不是好友发送失败总数
        $receiveLimitTotal = $contactMessageBatchSendResultService->getContactMessageBatchSendResultCount([
            ['batch_id', '=', $batchId],
            ['status', '=', MessageStatus::NOT_FRIEND],
        ]);

        // 获取客户接收已达上限总数
        $notFriendTotal = $contactMessageBatchSendResultService->getContactMessageBatchSendResultCount([
            ['batch_id', '=', $batchId],
            ['status', '=', MessageStatus::RECEIVE_LIMIT],
        ]);

        // 更新群发状态
        $contactMessageBatchSendService->updateContactMessageBatchSendById($batchId, [
            'sendTotal' => $sendTotal,
            'notSendTotal' => $sendEmployeeTotal - $sendTotal,
            'receivedTotal' => $receivedTotal,
            'notReceivedTotal' => $sendContactTotal - $receivedTotal,
            'receiveLimitTotal' => $receiveLimitTotal,
            'notFriendTotal' => $notFriendTotal,
        ]);
    }

    private function getGroupTask(int $corpId, string $msgId, int $limit = 500, string $cursor = '')
    {
        /** @var WeWorkFactory $weWorkFactory */
        $weWorkFactory = make(WeWorkFactory::class);
        $weWorkContactApp = $weWorkFactory->getContactApp($corpId);
        return $weWorkContactApp->external_contact_message->httpPostJson('cgi-bin/externalcontact/get_groupmsg_task', [
            'msgid' => $msgId,
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }
}
