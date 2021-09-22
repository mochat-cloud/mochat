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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\App\WorkContact\Event\AddContactRawEvent;
use MoChat\App\WorkContact\Logic\SyncContactByEmployeeLogic;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 添加企业客户事件.
 *
 * @Listener(priority=9999)
 */
class AddContactRawListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var WorkContactContract
     */
    private $workContactService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function listen(): array
    {
        return [
            AddContactRawEvent::class,
        ];
    }

    /**
     * @param AddContactRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        $this->logger = $this->container->get(StdoutLoggerInterface::class);
        $this->workContactService = $this->container->get(WorkContactContract::class);
        $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        $this->logger->info('触发添加好友回调了：' . json_encode($message, JSON_THROW_ON_ERROR));

        // 校验 - 微信回调参数
        if (empty($message) || empty($message['ToUserName']) || empty($message['UserID']) || empty($message['ExternalUserID'])) {
            return;
        }

        $syncLogic = make(SyncContactByEmployeeLogic::class);
        [$contactId, $isNewContact, $employee] = $syncLogic->handle($message['ToUserName'], $message['UserID'], $message['ExternalUserID'], $message);

        if ($contactId === 0) {
            return;
        }

        // 触发添加客户事件
        $state = isset($message['State']) ? $message['State'] : '';
        $welcomeCode = isset($message['WelcomeCode']) ? $message['WelcomeCode'] : '';
        $this->triggerAddContactEvent($contactId, (int) $employee['id'], (string) $state, $isNewContact, $welcomeCode);
    }

    /**
     * 触发添加客户事件.
     */
    private function triggerAddContactEvent(int $contactId, int $employeeId, string $state, int $isNewContact, string $welcomeCode)
    {
        $contact = $this->workContactService->getWorkContactById($contactId);
        if (empty($contact)) {
            return;
        }

        $contact['employeeId'] = $employeeId;
        $contact['state'] = $state;
        $contact['isNew'] = $isNewContact;
        $contact['welcomeCode'] = $welcomeCode;

        go(function () use ($contact) {
            $this->eventDispatcher->dispatch(new AddContactEvent($contact));
        });
    }
}
