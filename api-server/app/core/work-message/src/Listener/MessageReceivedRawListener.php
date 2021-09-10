<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\WorkMessage\Event\MessageReceivedRawEvent;
use MoChat\App\WorkMessage\Logic\StoreLogic;
use Psr\Container\ContainerInterface;

/**
 * 收到原始消息.
 *
 * @Listener
 */
class MessageReceivedRawListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    public function listen(): array
    {
        return [
            MessageReceivedRawEvent::class,
        ];
    }

    /**
     * @param MessageReceivedRawEvent $event
     */
    public function process(object $event)
    {
        $corpId = (int) $event->corpId;
        $messages = $event->messages;
        $messageStoreLogic = $this->container->get(StoreLogic::class);
        $messageStoreLogic->handle($corpId, $messages);
    }
}
