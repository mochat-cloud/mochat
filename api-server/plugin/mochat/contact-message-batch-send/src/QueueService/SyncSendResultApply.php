<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\Plugin\ContactMessageBatchSend\Constants\MessageStatus;
use MoChat\Plugin\ContactMessageBatchSend\Constants\Status;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;
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
     * @param int $batchEmployeeId 群发成员ID
     */
    public function handle(int $batchEmployeeId): void
    {
        Db::beginTransaction();
        try {
            $contactMessageBatchSendEmployee = $this->container->get(ContactMessageBatchSendEmployeeContract::class);
            $batchEmployee                   = $contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeeLockById($batchEmployeeId, ['id', 'batch_id', 'status', 'msg_id']);
            if (empty($batchEmployee) || $batchEmployee['status'] === Status::SEND_OK) {
                Db::commit();
                return;
            }
            $batch = $this->container->get(ContactMessageBatchSendContract::class)->getContactMessageBatchSendLockById(
                $batchEmployee['batchId'],
                ['id', 'corp_id', 'send_employee_total', 'send_contact_total']
            );
            if (empty($batch)) {
                Db::commit();
                return;
            }
            $contactMessageBatchSendResult = $this->container->get(ContactMessageBatchSendResultContract::class);
            $app                           = $this->wxApp($batch['corpId'], 'contact')->external_contact_message;
            $batchResult                   = $app->get($batchEmployee['msgId']);

            if ($batchResult['errcode'] > 0) {
                ## 更新成员状态
                $contactMessageBatchSendEmployee->updateContactMessageBatchSendEmployeeById($batchEmployeeId, [
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
                $row = $contactMessageBatchSendResult->getContactMessageBatchSendResultByBatchUserId($batchEmployee['batchId'], $result['external_userid'], ['id']);
                if (! empty($row)) {
                    ## 同步结果
                    $contactMessageBatchSendResult->updateContactMessageBatchSendResultById($row['id'], [
                        'userId'   => $result['userid'],
                        'status'   => $result['status'],
                        'sendTime' => $result['send_time'] ?? 0,
                    ]);
                }
            }

            ## 更新成员状态
            $contactMessageBatchSendEmployee->updateContactMessageBatchSendEmployeeById($batchEmployeeId, [
                'errCode'  => $batchResult['errcode'],
                'errMsg'   => $batchResult['errmsg'],
                'status'   => $sendStatus,
                'sendTime' => $sendTime,
            ]);

            ## 获取已发送成员总数
            $sendTotal = $contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeeCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', Status::SEND_OK],
            ]);
            ## 获取已送达客户总数
            $receivedTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', MessageStatus::SEND_OK],
            ]);
            ## 获取客户因不是好友发送失败总数
            $receiveLimitTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', MessageStatus::NOT_FRIEND],
            ]);
            ## 获取客户接收已达上限总数
            $notFriendTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', MessageStatus::RECEIVE_LIMIT],
            ]);
            ## 更新群发状态
            $this->container->get(ContactMessageBatchSendContract::class)->updateContactMessageBatchSendById($batch['id'], [
                'sendTotal'         => $sendTotal,
                'notSendTotal'      => $batch['sendEmployeeTotal'] - $sendTotal,
                'receivedTotal'     => $receivedTotal,
                'notReceivedTotal'  => $batch['sendContactTotal'] - $receivedTotal,
                'receiveLimitTotal' => $receiveLimitTotal,
                'notFriendTotal'    => $notFriendTotal,
            ]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('%s [%s] %s', '客户群发消息同步结果失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->container->get(StdoutLoggerInterface::class)->error($e->getTraceAsString());
        }
    }
}
