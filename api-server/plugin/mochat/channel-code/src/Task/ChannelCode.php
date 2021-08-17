<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Task;

use Hyperf\Crontab\Annotation\Crontab;
use MoChat\Plugin\ChannelCode\Logic\ChannelCodeLogic;

/**
 * @Crontab(name="channelCode", rule="*\/30 * * * * *", callback="execute", enable="isEnable", singleton=true, memo="渠道码-更新客户联系我方式")
 */
class ChannelCode
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(): void
    {
        (new ChannelCodeLogic())->handle();
    }

    public function isEnable(): bool
    {
        return true;
    }
}
