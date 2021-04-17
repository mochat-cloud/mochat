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

use App\Constants\MessageBatchSend\Status;
use App\Contract\ContactMessageBatchSendEmployeeServiceInterface;
use App\Contract\ContactMessageBatchSendResultServiceInterface;
use App\Contract\ContactMessageBatchSendServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

class StoreApply
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
     * @param  int  $batchId  群发ID
     */
    public function handle(int $batchId): void
    {
        Db::beginTransaction();
        try {
            $batch = $this->container->get(ContactMessageBatchSendServiceInterface::class)->getContactMessageBatchSendLockById($batchId);
            if (empty($batch) || $batch['sendStatus'] === Status::SEND_OK) {
                Db::commit();
                return;
            }
            $contactMessageBatchSendEmployee = $this->container->get(ContactMessageBatchSendEmployeeServiceInterface::class);
            $contactMessageBatchSendResult   = $this->container->get(ContactMessageBatchSendResultServiceInterface::class);
            $app                             = $this->wxApp($batch['corpId'], 'contact')->external_contact_message;
            $batchEmployees                  = $contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeesByBatchId($batchId, [], ['id', 'employee_id', 'wx_user_id', 'content']);
            foreach ($batchEmployees as $employee) {
                $externalUserId             = $contactMessageBatchSendResult->getExternalUserIdsByBatchEmployeeId($batchId, $employee['employeeId']);
                $content                    = $employee['content'];
                $content['external_userid'] = $externalUserId;
                $content['sender']          = $employee['wxUserId'];
                ## 推送消息
                $batchResult = $app->submit($content);
                ## 更新状态
                $contactMessageBatchSendEmployee->updateContactMessageBatchSendEmployeeById($employee['id'], [
                    'errCode' => $batchResult['errcode'],
                    'errMsg'  => $batchResult['errmsg'],
                    'msgId'   => $batchResult['msgid'] ?? '',
                ]);
            }
            ## 更新群发状态
            $this->container->get(ContactMessageBatchSendServiceInterface::class)->updateContactMessageBatchSendById($batchId, [
                'sendStatus' => Status::SEND_OK,
                'sendTime'   => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('%s [%s] %s', '客户群发消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->container->get(StdoutLoggerInterface::class)->error($e->getTraceAsString());
        }

    }
}
