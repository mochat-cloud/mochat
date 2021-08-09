<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Constants;

use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class Status
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
     * @Message("发送失败")
     */
    public const SEND_FAIL = 2;
}
