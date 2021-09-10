<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\SyncData\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use MoChat\App\SyncData\DynamicCrontabManager;

class DynamicCrontabRegisterListener implements ListenerInterface
{
    /**
     * @var DynamicCrontabManager
     */
    protected $dynamicCrontabManager;

    public function __construct(DynamicCrontabManager $dynamicCrontabManager)
    {
        $this->dynamicCrontabManager = $dynamicCrontabManager;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeProcessHandle::class,
            BeforeCoroutineHandle::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $this->dynamicCrontabManager->register();
    }
}
