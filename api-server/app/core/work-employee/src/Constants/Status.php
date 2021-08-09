<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\Constants;

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
    public const ACTIVE = 1;

    /**
     * @Message("已禁用")
     */
    public const DISABLED = 2;

    /**
     * @Message("未激活")
     */
    public const NOTACTIVE = 4;

    /**
     * @Message("退出企业")
     */
    public const QUIT = 5;

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
