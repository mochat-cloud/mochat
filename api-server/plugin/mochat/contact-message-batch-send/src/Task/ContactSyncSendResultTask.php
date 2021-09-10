<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Queue\ContactMessageBatchSendQueue;

/**
 * 同步客户群发结果任务(每小时同步一次，仅同步最近一周内的).
 *
 * @Crontab(name="ContactSyncSendResultTask", rule="0 * * * *", callback="execute", memo="同步客户群发结果任务")
 */
class ContactSyncSendResultTask
{
    /**
     * @Inject
     * @var ContactMessageBatchSendQueue
     */
    private $contectMessageBatchSendQueue;

    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute()
    {
        try {
            $batchEmployeeIds = $this->contactMessageBatchSendEmployeeService->getContactMessageBatchSendEmployeeIdsByLastWeek();
            if (empty($batchEmployeeIds)) {
                return;
            }

            foreach ($batchEmployeeIds as $batchEmployeeId) {
                $this->contectMessageBatchSendQueue->syncSendResult(['batchEmployeeId' => $batchEmployeeId]);
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '同步客户群发结果任务失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
