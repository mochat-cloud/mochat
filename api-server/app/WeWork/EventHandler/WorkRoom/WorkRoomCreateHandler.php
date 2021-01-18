<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\WeWork\EventHandler\WorkRoom;

use App\QueueService\WorkRoom\UpdateCallback;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 客户群创建 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_external_chat/create")
 * Class WorkRoomCreateHandler
 */
class WorkRoomCreateHandler extends AbstractEventHandler
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return null|mixed|void
     */
    public function process()
    {
        ## 队列
        (new UpdateCallback())->handle($this->message);
    }
}
