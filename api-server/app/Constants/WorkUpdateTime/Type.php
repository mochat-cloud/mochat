<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkUpdateTime;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Type extends AbstractConstants
{
    /**
     * @Message("通讯录")
     */
    const EMPLOYEE = 1;

    /**
     * @Message("客户")
     */
    const CONTACT = 2;

    /**
     * @Message("标签")
     */
    const TAG = 3;

    /**
     * @Message("部门")
     */
    const DEPARTMENT = 4;

    /**
     * @Message("企业数据")
     */
    const CORP_DATA = 6;
}
