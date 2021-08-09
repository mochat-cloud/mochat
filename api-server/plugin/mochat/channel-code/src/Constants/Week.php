<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 渠道码周期枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Week extends AbstractConstants
{
    /**
     * @Message("周日")
     */
    public const SUNDAY = 0;

    /**
     * @Message("周一")
     */
    public const MONDAY = 1;

    /**
     * @Message("周二")
     */
    public const TUESDAY = 2;

    /**
     * @Message("周三")
     */
    public const WEDNESDAY = 3;

    /**
     * @Message("周四")
     */
    public const THURSDAY = 4;

    /**
     * @Message("周五")
     */
    public const FRIDAY = 5;

    /**
     * @Message("周六")
     */
    public const SATURDAY = 6;
}
