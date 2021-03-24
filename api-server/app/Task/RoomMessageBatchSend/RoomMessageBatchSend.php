<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Task\RoomMessageBatchSend;

use App\Contract\RoomMessageBatchSendServiceInterface;
use App\QueueService\RoomMessageBatchSend\StoreApply;
use Hyperf\Crontab\Annotation\Crontab;
use Psr\Container\ContainerInterface;

/**
 * @Crontab(name="roomMessageBatchSend", rule="*\/1 * * * *", callback="execute", singleton=true, memo="客户群消息群发")
 */
class RoomMessageBatchSend
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
        $service    = $this->container->get(RoomMessageBatchSendServiceInterface::class);
        $rows       = $service->getRoomMessageBatchSendsBySend();

        foreach ($rows as $item) {
            $storeQueue->handle($item['id']);
        }

    }

}
