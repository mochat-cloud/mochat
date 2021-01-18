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

use App\QueueService\WorkContact\UpdateContactCallBackApply;
use Hyperf\Logger\LoggerFactory;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 编辑企业客户 - 事件回调（修改外部联系人的备注、手机号或标签时，回调该事件）.
 * @WeChatEventHandler(eventPath="event/change_external_contact/edit_external_contact")
 * Class ContactUpdateHandler
 */
class ContactUpdateHandler extends AbstractEventHandler
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @return null|mixed|void
     */
    public function process()
    {
        $logger = make(LoggerFactory::class)->get('log', 'default');
        $logger->error('修改客户信息回调日志', $this->message);
        ## 队列
        make(UpdateContactCallBackApply::class)->handle($this->message);
    }
}
