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
 * @method static getMessage($code, array $options = []) 获取是否配置客户联系枚举值
 */
class ContactAuth extends AbstractConstants
{
    /**
     * @Message("是")
     */
    const YES = 1;

    /**
     * @Message("否")
     */
    const NO = 2;

    /**
     * @var array 数据列表
     */
    public static $optionData = [
        self::YES,
        self::NO,
    ];
}
