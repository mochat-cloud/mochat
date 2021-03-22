<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\QueueService\ContactMessageBatchSend;

use App\Contract\ContactMessageBatchSendEmployeeServiceInterface;
use App\Contract\ContactMessageBatchSendResultServiceInterface;
use App\Contract\ContactMessageBatchSendServiceInterface;
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
            $contactMessageBatchSendEmployee = $this->container->get(ContactMessageBatchSendEmployeeServiceInterface::class);
            $batchEmployee                   = $contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeeLockById($batchEmployeeId, ['id', 'batch_id', 'status', 'msg_id']);
            if (empty($batchEmployee) || $batchEmployee['status'] === 1) {
                Db::commit();
                return;
            }
            $batch = $this->container->get(ContactMessageBatchSendServiceInterface::class)->getContactMessageBatchSendLockById($batchEmployee['batchId'],
                ['id', 'corp_id', 'send_employee_total', 'send_contact_total']);
            if (empty($batch)) {
                Db::commit();
                return;
            }
            $contactMessageBatchSendResult = $this->container->get(ContactMessageBatchSendResultServiceInterface::class);
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

            $sendStatus = 0;
            $sendTime   = null;

            foreach ($batchResult['detail_list'] as $result) {
                if ($result['status'] == 0) {
                    Db::commit();
                    return;
                }
                if ($sendStatus == 0) {
                    $sendStatus = 1;
                    $sendTime   = date('Y-m-d H:i:s', $result['send_time']);
                }
                $row = $contactMessageBatchSendResult->getContactMessageBatchSendResultByBatchUserId($batchEmployee['batchId'], $result['external_userid'], ['id']);
                if (!empty($row)) {
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
                ['status', '=', 1],
            ]);
            ## 获取已送达客户总数
            $receivedTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', 1],
            ]);
            ## 获取客户因不是好友发送失败总数
            $receiveLimitTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', 2],
            ]);
            ## 获取客户接收已达上限总数
            $notFriendTotal = $contactMessageBatchSendResult->getContactMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['status', '=', 3],
            ]);
            ## 更新群发状态
            $this->container->get(ContactMessageBatchSendServiceInterface::class)->updateContactMessageBatchSendById($batch['id'], [
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
            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('%s [%s] %s', '客户消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->container->get(StdoutLoggerInterface::class)->error($e->getTraceAsString());
        }

    }
}
