<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Event\Tag;

/**
 * 企业客户标签重排事件
 * 当企业管理员在终端/管理端调整标签顺序时，可能导致标签顺序整体调整重排，
 * 引起大部分标签的order值发生变化，此时会回调此事件，收到此事件后企业应尽快
 * 全量同步标签的order值，防止后续调用接口排序出现非预期结果。
 */
class ShuffleTagRawEvent
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
