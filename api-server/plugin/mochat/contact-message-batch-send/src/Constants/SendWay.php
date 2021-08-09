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
class SendWay
{
    /**
     * @Message("立即发送")
     */
    public const SEND_NOW = 1;

    /**
     * @Message("定时发送")
     */
    public const SEND_DELAY = 2;
}
