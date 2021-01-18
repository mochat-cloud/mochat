<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Listener;

use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class QueueFailedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('queue');
    }

    public function listen(): array
    {
        return [
            FailedHandle::class,
        ];
    }

    /**
     * @param FailedHandle $event
     */
    public function process(object $event): void
    {
        //dump($event->getThrowable()->getMessage(),$event->getThrowable()->getTrace());
        $this->logger->error(sprintf('[redis异步队列错误-message:] %s', $event->getThrowable()->getMessage()));
        $this->logger->error(sprintf('[redis异步队列错误-trace:] %s', $event->getThrowable()->getTraceAsString()));
    }
}
