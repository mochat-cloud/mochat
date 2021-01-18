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

use App\QueueService\WorkContact\AddContactCallback;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 添加企业客户 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_external_contact/add_external_contact")
 * Class ContactStoreHandler
 */
class ContactStoreHandler extends AbstractEventHandler
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return null|mixed|void
     */
    public function process()
    {
        ## 队列
        (new AddContactCallback())->handle($this->message);
    }
}
