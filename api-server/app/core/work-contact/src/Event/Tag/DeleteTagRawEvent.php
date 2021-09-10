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
 * 企业客户标签删除事件
 * 当企业客户标签/标签组被删除改时，回调此事件。
 * 删除标签组时，该标签组下的所有标签将被同时删除，但不会进行回调。
 */
class DeleteTagRawEvent
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
