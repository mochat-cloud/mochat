<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Queue;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\WorkMessage\Event\MessageReceivedRawEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class MessageReceivedRawQueue
{
    /**
     * @AsyncQueueMessage(pool="chat")
     * @param int $corpId 企业ID
     */
    public function handle(int $corpId, array $messages): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = make(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new MessageReceivedRawEvent($corpId, $messages));
    }
}
