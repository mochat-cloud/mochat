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
 * 客户群变更事件
 * 客户群被修改后（群名变更，群成员增加或移除，群主变更，群公告变更），回调该事件。
 * 收到该事件后，企业需要再调用获取客户群详情接口，以获取最新的群详情。
 */
class UpdateRoomRawEvent
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
