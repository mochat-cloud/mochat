<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Common\Constants\BusinessLog;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 日志事件枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Event extends AbstractConstants
{
    /**
     * @Message("新建渠道码")
     */
    public const CHANNEL_CODE_CREATE = 100;

    /**
     * @Message("编辑渠道码")
     */
    public const CHANNEL_CODE_UPDATE = 101;

    /**
     * @Message("新建自动拉群")
     */
    public const ROOM_AUTO_PULL_CREATE = 200;

    /**
     * @Message("编辑自动拉群")
     */
    public const ROOM_AUTO_PULL_UPDATE = 201;

    /**
     * @Message("新建欢迎语")
     */
    public const GREETING_CREATE = 300;

    /**
     * @Message("编辑欢迎语")
     */
    public const GREETING_UPDATE = 301;

    /**
     * @Message("新建敏感词")
     */
    public const SENSITIVE_WORD_CREATE = 302;

    /**
     * @Message("编辑敏感词")
     */
    public const SENSITIVE_WORD_UPDATE = 303;
}
