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
 * 渠道码状态枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Status extends AbstractConstants
{
    /**
     * @Message("开启")
     */
    public const OPEN = 1;

    /**
     * @Message("关闭")
     */
    public const CLOSE = 2;
}
