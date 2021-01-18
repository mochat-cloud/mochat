<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Task;

use App\Logic\ChannelCode\ChannelCodeLogic;
use Hyperf\Crontab\Annotation\Crontab;

/**
 * @Crontab(name="channelCode", rule="*\/30 * * * * *", callback="execute", singleton=true, memo="渠道码-更新客户联系我方式")
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
}
