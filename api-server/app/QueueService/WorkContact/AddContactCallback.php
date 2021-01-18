<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContact;

use App\Logic\WorkContact\StoreCallback;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

/**
 * 企业微信-添加客户事件回调
 * Class AddContactCallBack.
 */
class AddContactCallback
{
    /**
     * @AsyncQueueMessage(pool="contact")
     * @param array $wxData 微信回调数据
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $wxData): void
    {
        (new StoreCallback())->handle($wxData);
    }
}
