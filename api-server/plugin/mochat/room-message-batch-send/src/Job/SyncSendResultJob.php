<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\Plugin\RoomMessageBatchSend\Constants\MessageStatus;
use MoChat\Plugin\RoomMessageBatchSend\Constants\Status;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendEmployeeContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendResultContract;

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

        $roomMessageBatchSendService = make(RoomMessageBatchSendContract::class);
        $roomMessageBatchSendEmployeeService = make(RoomMessageBatchSendEmployeeContract::class);
        $logger = make(StdoutLoggerInterface::class);

        Db::beginTransaction();
        try {
            $batchEmployee = $roomMessageBatchSendEmployeeService->getRoomMessageBatchSendEmployeeLockById($batchEmployeeId, ['id', 'batch_id', 'status', 'msg_id']);
            if (empty($batchEmployee) || $batchEmployee['status'] === Status::SEND_OK) {
                Db::commit();
                return;
            }

            $batch = $roomMessageBatchSendService->getRoomMessageBatchSendLockById(
                $batchEmployee['batchId'],
                ['id', 'corp_id', 'send_employee_total', 'send_room_total']
            );

            if (empty($batch)) {
                Db::commit();
                return;
            }

            $batchResult = $this->getGroupTask((int) $batch['corpId'], $batchEmployee['msgId']);

            if ($batchResult['errcode'] > 0) {
                $logger->error(sprintf('%s [%s] %s', '客户群群发消息同步结果失败', date('Y-m-d H:i:s'), $batchResult['errmsg']));
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
                $roomMessageBatchSendEmployeeService->updateRoomMessageBatchSendEmployeeById($batchEmployeeId, [
                    'errCode' => $batchResult['errcode'],
                    'errMsg' => $batchResult['errmsg'],
                    'status' => $sendStatus,
                    'sendTime' => $sendTime,
                ]);

                $this->updateContactMessageSendResult($batch['corpId'], $batchEmployee['batchId'], $batchEmployee['msgId'], $result['userid'], 500, '');
            }

            $this->updateSendTotal($batchEmployee['batchId'], $batch['sendEmployeeTotal'], $batch['sendRoomTotal']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $logger->error(sprintf('%s [%s] %s', '客户群群发消息同步结果失败', date('Y-m-d H:i:s'), $e->getMessage()));
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

    private function updateSendTotal(int $batchId, int $sendEmployeeTotal, int $sendRoomTotal)
    {
        $roomMessageBatchSendService = make(RoomMessageBatchSendContract::class);
        $roomMessageBatchSendEmployeeService = make(RoomMessageBatchSendEmployeeContract::class);
        $roomMessageBatchSendResultService = make(RoomMessageBatchSendResultContract::class);

        // 获取已发送成员总数
        $sendTotal = $roomMessageBatchSendEmployeeService->getRoomMessageBatchSendEmployeeCount([
            ['batch_id', '=', $batchId],
            ['status', '=', Status::SEND_OK],
        ]);

        // 获取已送达客户总数
        $receivedTotal = $roomMessageBatchSendResultService->getRoomMessageBatchSendResultCount([
            ['batch_id', '=', $batchId],
            ['status', '=', MessageStatus::SEND_OK],
        ]);

        // 更新群发状态
        $roomMessageBatchSendService->updateRoomMessageBatchSendById($batchId, [
            'sendTotal' => $sendTotal,
            'notSendTotal' => $sendEmployeeTotal - $sendTotal,
            'receivedTotal' => $receivedTotal,
            'notReceivedTotal' => $sendRoomTotal - $receivedTotal,
        ]);
    }

    /**
     * 获取客户发送结果.
     */
    private function updateContactMessageSendResult(int $corpId, int $batchId, string $msgId, string $employeeUserId, int $limit = 500, string $cursor = '')
    {
        $logger = make(StdoutLoggerInterface::class);
        $roomMessageBatchSendResultService = make(RoomMessageBatchSendResultContract::class);

        $sendResult = $this->getGroupSendResult($corpId, $msgId, $employeeUserId, $limit, $cursor);

        if ($sendResult['errcode'] !== 0) {
            $logger->error(sprintf('%s [%s] %s', '客户群群发消息客户同步结果失败', date('Y-m-d H:i:s'), $sendResult['errmsg']));
            return;
        }

        if (empty($sendResult['send_list'])) {
            return;
        }

        foreach ($sendResult['send_list'] as $result) {
            $row = $roomMessageBatchSendResultService->getRoomMessageBatchSendResultByBatchRoomId($batchId, $result['chat_id'], ['id']);
            if (! empty($row)) {
                // 同步结果
                $roomMessageBatchSendResultService->updateRoomMessageBatchSendResultById($row['id'], [
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
