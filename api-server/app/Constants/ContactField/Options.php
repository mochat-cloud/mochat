<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\ContactField;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static string getMessage(int $code)  获取字段类型
 */
class Options extends AbstractConstants
{
    /**
     * @Message("文本")
     */
    public const TEXT = 0;

    /**
     * @Message("单选")
     */
    public const RADIO = 1;

    /**
     * @Message("多选")
     */
    public const CHECKBOX = 2;

    /**
     * @Message("下拉")
     */
    public const SELECT = 3;

    /**
     * @Message("文件")
     */
    public const FILE = 4;

    /**
     * @Message("文本域")
     */
    public const TEXTAREA = 5;

    /**
     * @Message("日期")
     */
    public const DATE = 6;

    /**
     * @Message("日期时间")
     */
    public const DATETIME = 7;

    /**
     * @Message("数字")
     */
    public const NUMBER = 8;

    /**
     * @Message("手机号")
     */
    public const PHONE = 9;

    /**
     * @Message("邮箱")
     */
    public const EMAIL = 10;

    /**
     * @Message("图片")
     */
    public const PICTURE = 11;
}
