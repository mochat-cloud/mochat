<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\ChatTool;

use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class Status
{
    /**
     * @Message("状态-可用")
     */
    public const STATUS_YES = 1;

    /**
     * @Message("状态-不可用")
     */
    public const STATUS_NO = 0;
}
