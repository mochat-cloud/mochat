<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\EventHandler\Tag;

use MoChat\App\WorkContact\QueueService\Tag\DestroyCallBackApply;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 删除企业客户标签 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_external_tag/delete")
 * Class ContactStoreHandler
 */
class DestroyHandler extends AbstractEventHandler
{
    /**
     * @return null|mixed|void
     */
    public function process()
    {
        ## 队列
        (new DestroyCallBackApply())->handle($this->message);
    }
}
