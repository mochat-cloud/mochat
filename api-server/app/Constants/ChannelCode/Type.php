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
 * 渠道码类型枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Type extends AbstractConstants
{
    /**
     * @Message("单人")
     */
    const SINGLE_PERSON = 1;

    /**
     * @Message("多人")
     */
    const MANY_PEOPLE = 2;
}
