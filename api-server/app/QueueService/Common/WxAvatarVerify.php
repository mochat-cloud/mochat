<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\Common;

use App\Logic\WeWork\WxAvatarVerifyLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

class WxAvatarVerify
{
    /**
     * @AsyncQueueMessage(pool="coOSS", maxAttempts=1)
     * @param int $corpId 企业ID
     * @param string $type 类型
     */
    public function handle(int $corpId, string $type = 'user'): void
    {
        $logic = make(WxAvatarVerifyLogic::class);
        $logic->handle($corpId, $type);
    }
}
