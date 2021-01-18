<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkRoomAutoPull;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class IsVerified extends AbstractConstants
{
    /**
     * @Message("需验证")
     */
    const VERIFICATION = 1;

    /**
     * @Message("直接通过")
     */
    const PASS = 2;
}
