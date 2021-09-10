<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkRoom\Event;

/**
 * 客户群解散事件
 * 当客户群被群主解散后，回调该事件。
 * 需注意的是，如果发生群信息变动，会立即收到此事件，但是部分信息是异步处理，
 * 可能需要等一段时间(例如2秒)调用获取客户群详情接口才能得到最新结果.
 */
class DismissRoomRawEvent
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
