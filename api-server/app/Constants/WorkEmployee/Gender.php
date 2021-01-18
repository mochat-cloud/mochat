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
 * @method static getMessage($code, array $options = []) 获取性别枚举值
 */
class Gender extends AbstractConstants
{
    /**
     * @Message("未定义")
     */
    const UNDEFINED = 0;

    /**
     * @Message("男")
     */
    const MAN = 1;

    /**
     * @Message("女")
     */
    const WOMAN = 2;

    /**
     * @var array 数据列表
     */
    public static $optionData = [
        self::UNDEFINED,
        self::MAN,
        self::WOMAN,
    ];
}
