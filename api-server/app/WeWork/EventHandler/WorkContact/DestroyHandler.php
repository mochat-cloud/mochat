<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\WeWork\EventHandler\WorkContact;

use App\Constants\WorkContactEmployee\Status;
use App\Logic\WorkContact\DestroyCallBackLogic;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 删除企业客户 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_external_contact/del_external_contact")
 * Class DestroyHandler
 */
class DestroyHandler extends AbstractEventHandler
{
    /**
     * @return null|mixed|void
     */
    public function process()
    {
        ## 队列
        make(DestroyCallBackLogic::class)->handle($this->message, Status::REMOVE);
    }
}
