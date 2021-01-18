<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\ChannelCode;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 渠道码欢迎语类型枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class WelcomeType extends AbstractConstants
{
    /**
     * @Message("通用欢迎语")
     */
    const GENERAL = 1;

    /**
     * @Message("周期欢迎语")
     */
    const PERIODIC = 2;

    /**
     * @Message("特殊时期欢迎语")
     */
    const SPECIAL_PERIOD = 3;
}
