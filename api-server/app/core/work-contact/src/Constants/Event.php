<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 互动轨迹事件枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Event extends AbstractConstants
{
    /**
     * @Message("添加客户")
     */
    public const CREATE = 1;

    /**
     * @Message("打标签")
     */
    public const TAG = 2;

    /**
     * @Message("修改客户信息")
     */
    public const INFO = 3;

    /**
     * @Message("编辑用户画像")
     */
    public const USER_PORTRAIT = 4;

    /**
     * @Message("跟进状态")
     */
    public const PROCESS_STATUS = 5;

    /**
     * @Message("跟进记录")
     */
    public const PROCESS_RECORD = 6;

    /**
     * @Message("模板记录")
     */
    public const TEMPLATE = 7;

    /**
     * @Message("待办事项")
     */
    public const TODO = 8;
}
