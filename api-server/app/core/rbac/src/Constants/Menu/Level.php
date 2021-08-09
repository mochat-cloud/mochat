<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Rbac\Constants\Menu;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class Level extends AbstractConstants
{
    /**
     * @Message("一级菜单")
     */
    public const FIRST_LEVEL = 1;

    /**
     * @Message("二级菜单")
     */
    public const SECOND_LEVEL = 2;

    /**
     * @Message("三级菜单")
     */
    public const THIRD_LEVEL = 3;

    /**
     * @Message("四级菜单")
     */
    public const FOURTH_LEVEL = 4;

    /**
     * @Message("五级菜单")
     */
    public const FIFTH_LEVEL = 5;
}
