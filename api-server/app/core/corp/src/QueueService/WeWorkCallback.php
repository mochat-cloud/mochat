<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Corp\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\WorkContact\Event\AddContactRawEvent;
use MoChat\App\WorkContact\Event\AddHalfContactRawEvent;
use MoChat\App\WorkContact\Event\DeleteContactRawEvent;
use MoChat\App\WorkContact\Event\Employee\DeleteFollowEmployeeRawEvent;
use MoChat\App\WorkContact\Event\Tag\CreateTagRawEvent;
use MoChat\App\WorkContact\Event\Tag\DeleteTagRawEvent;
use MoChat\App\WorkContact\Event\Tag\ShuffleTagRawEvent;
use MoChat\App\WorkContact\Event\Tag\UpdateContactTagRawEvent;
use MoChat\App\WorkContact\Event\Tag\UpdateTagRawEvent;
use MoChat\App\WorkContact\Event\TransferFailRawEvent;
use MoChat\App\WorkContact\Event\UpdateContactRawEvent;
use MoChat\App\WorkDepartment\Event\CreateDepartmentRawEvent;
use MoChat\App\WorkDepartment\Event\DeleteDepartmentRawEvent;
use MoChat\App\WorkDepartment\Event\UpdateDepartmentRawEvent;
use MoChat\App\WorkEmployee\Event\CreateEmployeeRawEvent;
use MoChat\App\WorkEmployee\Event\DeleteEmployeeRawEvent;
use MoChat\App\WorkEmployee\Event\UpdateEmployeeRawEvent;
use MoChat\App\WorkMessage\Event\MessageArchiveRawEvent;
use MoChat\App\WorkMessage\Event\MessageNotifyRawEvent;
use MoChat\App\WorkRoom\Event\CreateRoomRawEvent;
use MoChat\App\WorkRoom\Event\DismissRoomRawEvent;
use MoChat\App\WorkRoom\Event\UpdateRoomRawEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 企业微信回调队列.
 */
class WeWorkCallback
{
    /**
     * @AsyncQueueMessage(pool="callback")
     */
    public function handle(array $message)
    {
        $wxEventPathArr = [];
        isset($message['MsgType']) && $wxEventPathArr['MsgType'] = $message['MsgType'];
        isset($message['Event']) && $wxEventPathArr['Event'] = $message['Event'];
        isset($message['ChangeType']) && $wxEventPathArr['EventType'] = $message['ChangeType'];
        isset($message['EventKey']) && $wxEventPathArr['EventType'] = $message['EventKey'];

        $wxEventPathStr = implode('.', $wxEventPathArr);
        $events = $this->getEvents();
        if (isset($events[$wxEventPathStr])) {
            $eventDispatcher = make(EventDispatcherInterface::class);
            $className = $events[$wxEventPathStr];
            $eventDispatcher->dispatch(new $className($message));
        }
    }

    protected function getEvents()
    {
        return [
            'event.change_external_contact.add_external_contact' => AddContactRawEvent::class,
            'event.change_external_contact.edit_external_contact' => UpdateContactRawEvent::class,
            'event.change_external_contact.add_half_external_contact' => AddHalfContactRawEvent::class,
            'event.change_external_contact.del_external_contact' => DeleteContactRawEvent::class,
            'event.change_external_contact.del_follow_user' => DeleteFollowEmployeeRawEvent::class,
            'event.change_external_contact.transfer_fail' => TransferFailRawEvent::class,
            'event.change_external_chat.create' => CreateRoomRawEvent::class,
            'event.change_external_chat.update' => UpdateRoomRawEvent::class,
            'event.change_external_chat.dismiss' => DismissRoomRawEvent::class,
            'event.change_external_tag.create' => CreateTagRawEvent::class,
            'event.change_external_tag.update' => UpdateTagRawEvent::class,
            'event.change_external_tag.delete' => DeleteTagRawEvent::class,
            'event.change_external_tag.shuffle' => ShuffleTagRawEvent::class,
            'event.change_contact.create_user' => CreateEmployeeRawEvent::class,
            'event.change_contact.update_user' => UpdateEmployeeRawEvent::class,
            'event.change_contact.delete_user' => DeleteEmployeeRawEvent::class,
            'event.change_contact.create_party' => CreateDepartmentRawEvent::class,
            'event.change_contact.update_party' => UpdateDepartmentRawEvent::class,
            'event.change_contact.delete_party' => DeleteDepartmentRawEvent::class,
            'event.change_contact.update_tag' => UpdateContactTagRawEvent::class,
            'event.conversation_archive' => MessageArchiveRawEvent::class,
            'event.msgaudit_notify' => MessageNotifyRawEvent::class,
        ];
    }
}
