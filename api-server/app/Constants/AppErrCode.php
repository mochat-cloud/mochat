<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use MoChat\Framework\Constants\ErrorCode;

/**
 * app错误code码
 * @Constants
 */
class AppErrCode extends ErrorCode
{
    /**
     * @Message("该用户无此权限，请联系管理员")
     * @HttpCode("403")
     */
    public const PERMISSION_DENY = 100100;
}
