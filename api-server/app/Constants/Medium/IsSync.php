<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\Medium;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static string getMessage(int $type)  获取类型文本
 */
class IsSync extends AbstractConstants
{
    /**
     * @Message("同步素材库")
     */
    public const SYNC = 1;

    /**
     * @Message("不同步素材库")
     */
    public const NO_SYNC = 2;
}
