<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkRoom\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Status extends AbstractConstants
{
    /**
     * @Message("正常")
     */
    public const NORMAL = 0;

    /**
     * @Message("跟进人离职")
     */
    public const QUIT = 1;

    /**
     * @Message("离职继承中")
     */
    public const QUIT_INHERIT = 2;

    /**
     * @Message("离职继承完成")
     */
    public const INHERIT_COMPLETE = 3;
}
