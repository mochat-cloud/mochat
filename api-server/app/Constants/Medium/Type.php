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
class Type extends AbstractConstants
{
    /**
     * @Message("文本")
     */
    public const TEXT = 1;

    /**
     * @Message("图片")
     */
    public const PICTURE = 2;

    /**
     * @Message("图文")
     */
    public const PICTURE_TEXT = 3;

    /**
     * @Message("音频")
     */
    public const VOICE = 4;

    /**
     * @Message("视频")
     */
    public const VIDEO = 5;

    /**
     * @Message("小程序")
     */
    public const MINI_PROGRAM = 6;

    /**
     * @Message("文件")
     */
    public const FILE = 7;
}
