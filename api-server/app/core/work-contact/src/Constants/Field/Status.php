<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Constants\Field;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class Status extends AbstractConstants
{
    /**
     * @Message("不展示")
     */
    public const NO_EXHIBITION = 0;

    /**
     * @Message("展示")
     */
    public const EXHIBITION = 1;
}
