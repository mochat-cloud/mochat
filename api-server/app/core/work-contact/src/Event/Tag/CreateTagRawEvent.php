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
 * 企业客户标签创建事件
 * 企业/管理员创建客户标签/标签组时（包括规则组的标签），回调此事件。
 * 收到该事件后，企业需要调用获取企业标签库来获取标签/标签组的详细信息。
 */
class CreateTagRawEvent
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
