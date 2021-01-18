<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkEmployee;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取状态枚举值
 */
class Status extends AbstractConstants
{
    /**
     * @Message("已激活")
     */
    const ACTIVE = 1;

    /**
     * @Message("已禁用")
     */
    const DISABLED = 2;

    /**
     * @Message("未激活")
     */
    const NOTACTIVE = 4;

    /**
     * @Message("退出企业")
     */
    const QUIT = 5;

    /**
     * @var array 数据列表
     */
    public static $optionData = [
        self::ACTIVE,
        self::DISABLED,
        self::NOTACTIVE,
        self::QUIT,
    ];
}
