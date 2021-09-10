<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Listener\Employee;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\WorkContact\Constants\Employee\Status;
use MoChat\App\WorkContact\Event\Employee\DeleteFollowEmployeeRawEvent;
use MoChat\App\WorkContact\Logic\DestroyCallBackLogic;
use Psr\Container\ContainerInterface;

/**
 * 删除跟进成员事件处理.
 *
 * @Listener(priority=9999)
 */
class DeleteFollowEmployeeRawListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    public function listen(): array
    {
        return [
            DeleteFollowEmployeeRawEvent::class,
        ];
    }

    /**
     * @param DeleteFollowEmployeeRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        make(DestroyCallBackLogic::class)->handle($message, Status::PASSIVE_REMOVE);
    }
}
