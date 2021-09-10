<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Constants;

use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class MessageStatus
{
    /**
     * @Message("未发送")
     */
    public const NOT_SEND = 0;

    /**
     * @Message("已发送")
     */
    public const SEND_OK = 1;

    /**
     * @Message("因客户不是好友导致发送失败")
     */
    public const NOT_FRIEND = 2;

    /**
     * @Message("因客户已经收到其他群发消息导致发送失败")
     */
    public const RECEIVE_LIMIT = 3;
}
