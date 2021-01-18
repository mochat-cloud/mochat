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
 * 渠道码自动添加好友枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class AutoAddFriend extends AbstractConstants
{
    /**
     * @Message("开启")
     */
    const OPEN = 1;  //无需验证 自动添加

    /**
     * @Message("关闭") //需要验证
     */
    const CLOSE = 2;
}
