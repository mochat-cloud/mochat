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

use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 客户群解散 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_external_chat/dismiss")
 * Class WorkRoomDismissHandler
 */
class WorkRoomDismissHandler extends AbstractEventHandler
{
    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return null|mixed|void
     */
    public function process()
    {
        ## 删除客户群
        $this->workRoomService->deleteWorkRoomByWxChatId($this->message['ChatId']);
    }
}
