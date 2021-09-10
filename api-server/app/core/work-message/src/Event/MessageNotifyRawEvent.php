<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Event;

/**
 * 产生会话回调事件
 * 为了提升企业会话存档的使用性能，降低无效的轮询次数。当企业收到或发送新消息时，
 * 企业微信可以以事件的形式推送到企业指定的url。回调间隔为15秒，在15秒内若有消息则触发回调，
 * 若无消息则不会触发回调。
 */
class MessageNotifyRawEvent
{
    /**
     * @var array
     */
    public $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }
}
