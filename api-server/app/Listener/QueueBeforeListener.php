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

use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class QueueBeforeListener implements ListenerInterface
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
            BeforeHandle::class,
        ];
    }

    /**
     * @param BeforeHandle $event
     */
    public function process(object $event): void
    {
        if (env('APP_ENV') === 'dev') {
            dump("redis异步队列开始执行中...[任务详情]:\n", $event->getMessage()->job());
        }
        $this->logger->debug(sprintf('redis异步队列开始执行中...[任务详情]::[<<<%s>>>]', serialize($event->getMessage()->job())));
    }
}
