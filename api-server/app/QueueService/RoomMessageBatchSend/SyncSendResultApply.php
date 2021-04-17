<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\QueueService\RoomMessageBatchSend;

use App\Constants\MessageBatchSend\MessageStatus;
use App\Constants\MessageBatchSend\Status;
use App\Contract\RoomMessageBatchSendEmployeeServiceInterface;
use App\Contract\RoomMessageBatchSendResultServiceInterface;
use App\Contract\RoomMessageBatchSendServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

class SyncSendResultApply
{
    use AppTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @AsyncQueueMessage(pool="contact")
     * @param  int  $batchEmployeeId  群发成员ID
     */
    public function handle(int $batchEmployeeId): void
    {
        Db::beginTransaction();
        try {
            $roomMessageBatchSendEmployee = $this->container->get(RoomMessageBatchSendEmployeeServiceInterface::class);
            $batchEmployee                = $roomMessageBatchSendEmployee->getRoomMessageBatchSendEmployeeLockById($batchEmployeeId, ['id', 'batch_id', 'status', 'msg_id']);
            if (empty($batchEmployee) || $batchEmployee['status'] === Status::SEND_OK) {
                Db::commit();
                return;
            }
            $batch = $this->container->get(RoomMessageBatchSendServiceInterface::class)->getRoomMessageBatchSendLockById($batchEmployee['batchId'],
                ['id', 'corp_id', 'send_employee_total', 'send_room_total']);
            if (empty($batch)) {
                Db::commit();
                return;
            }
            $roomMessageBatchSendResult = $this->container->get(RoomMessageBatchSendResultServiceInterface::class);
            $app                        = $this->wxApp($batch['corpId'], 'contact')->external_contact_message;
            $batchResult                = $app->get($batchEmployee['msgId']);

            if ($batchResult['errcode'] > 0) {
                ## 更新成员状态
                $roomMessageBatchSendEmployee->updateRoomMessageBatchSendEmployeeById($batchEmployeeId, [
                    'errCode' => $batchResult['errcode'],
                    'errMsg'  => $batchResult['errmsg'],
                ]);
                Db::commit();
                return;
            }

            $sendStatus = Status::NOT_SEND;
            $sendTime   = null;

            foreach ($batchResult['detail_list'] as $result) {
                if ($result['status'] == MessageStatus::NOT_SEND) {
                    Db::commit();
                    return;
                }
                if ($sendStatus == Status::NOT_SEND) {
                    $sendStatus = Status::SEND_OK;
                    $sendTime   = date('Y-m-d H:i:s', $result['send_time']);
                }
                $row = $roomMessageBatchSendResult->getRoomMessageBatchSendResultByBatchRoomId($batchEmployee['batchId'], $result['chat_id'], ['id']);
                if (!empty($row)) {
                    ## 同步结果
                    $roomMessageBatchSendResult->updateRoomMessageBatchSendResultById($row['id'], [
                        'userId'   => $result['userid'],
                        'status'   => $result['status'],
                        'sendTime' => $result['send_time'] ?? 0,
                    ]);
                }
            }

            ## 更新成员状态
            $roomMessageBatchSendEmployee->updateRoomMessageBatchSendEmployeeById($batchEmployeeId, [
                'errCode'  => $batchResult['errcode'],
                'errMsg'   => $batchResult['errmsg'],
                'status'   => $sendStatus,
                'sendTime' => $sendTime,
            ]);

            ## 获取已发送成员总数
            $sendTotal = $roomMessageBatchSendEmployee->getRoomMessageBatchSendEmployeeCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', Status::SEND_OK],
            ]);
            ## 获取已送达客户总数
            $receivedTotal = $roomMessageBatchSendResult->getRoomMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', MessageStatus::SEND_OK],
            ]);
            ## 更新群发状态
            $this->container->get(RoomMessageBatchSendServiceInterface::class)->updateRoomMessageBatchSendById($batch['id'], [
                'sendTotal'        => $sendTotal,
                'notSendTotal'     => $batch['sendEmployeeTotal'] - $sendTotal,
                'receivedTotal'    => $receivedTotal,
                'notReceivedTotal' => $batch['sendRoomTotal'] - $receivedTotal,
            ]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('%s [%s] %s', '客户群群发消息同步结果失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->container->get(StdoutLoggerInterface::class)->error($e->getTraceAsString());
        }

    }
}
