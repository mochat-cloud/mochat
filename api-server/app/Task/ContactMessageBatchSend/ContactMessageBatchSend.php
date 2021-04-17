<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Task\ContactMessageBatchSend;

use App\Contract\ContactMessageBatchSendServiceInterface;
use App\QueueService\ContactMessageBatchSend\StoreApply;
use Hyperf\Crontab\Annotation\Crontab;
use Psr\Container\ContainerInterface;

/**
 * @Crontab(name="contactMessageBatchSend", rule="*\/1 * * * *", callback="execute", singleton=true, memo="客户消息群发")
 */
class ContactMessageBatchSend
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(): void
    {
        $storeQueue = $this->container->get(StoreApply::class);
        $service    = $this->container->get(ContactMessageBatchSendServiceInterface::class);
        $rows       = $service->getContactMessageBatchSendsBySend();

        foreach ($rows as $item) {
            $storeQueue->handle($item['id']);
        }

    }

}
