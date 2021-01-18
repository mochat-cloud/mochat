<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkContactEmployee;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Status extends AbstractConstants
{
    /**
     * @Message("正常")
     */
    const NORMAL = 1;

    /**
     * @Message("成员删除客户")
     */
    const REMOVE = 2;

    /**
     * @Message("客户删除成员")
     */
    const PASSIVE_REMOVE = 3;

    /**
     * @Message("拉黑")
     */
    const BLACKLIST = 4;

    /**
     * @var array 数据列表
     */
    public static $optionData = [
        self::NORMAL,
        self::REMOVE,
        self::PASSIVE_REMOVE,
        self::BLACKLIST,
    ];
}
