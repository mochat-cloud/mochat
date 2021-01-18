<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkContactRoom;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class joinScene extends AbstractConstants
{
    /**
     * @Message("由成员邀请入群（直接邀请入群）")
     */
    const DIRECT_INVITE = 1;

    /**
     * @Message("由成员邀请入群（通过邀请链接入群）")
     */
    const LINK_INVITE = 2;

    /**
     * @Message("通过扫描群二维码入群")
     */
    const QRCODE = 3;
}
