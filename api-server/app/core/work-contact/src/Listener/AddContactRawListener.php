<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;
use MoChat\App\WorkContact\Logic\StoreCallback;
use Psr\Container\ContainerInterface;
use MoChat\App\WorkContact\Event\AddContactRawEvent;

/**
 * 添加企业客户事件
 *
 * @Listener(priority=9999)
 */
class AddContactRawListener implements ListenerInterface
{
    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;

    public function listen(): array
    {
        return [
            AddContactRawEvent::class
        ];
    }

    /**
     * @param AddContactRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        (new StoreCallback())->handle($message);
    }
}