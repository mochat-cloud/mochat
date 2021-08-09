<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Rbac\Constants\Role;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class PermissionType extends AbstractConstants
{
    /**
     * @Message("是")
     */
    public const START = 1; //查看部门数据

    /**
     * @Message("否")
     */
    public const DISABLE = 2; //查看个人数据
}
