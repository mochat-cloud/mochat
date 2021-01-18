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
 */
class Status extends AbstractConstants
{
    /**
     * @Message("不展示")
     */
    const NO_EXHIBITION = 0;

    /**
     * @Message("展示")
     */
    const EXHIBITION = 1;
}
