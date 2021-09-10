<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\Event;

/**
 * 更新成员事件.
 */
class UpdateEmployeeRawEvent
{
    /**
     * @var array
     */
    public $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }
}
