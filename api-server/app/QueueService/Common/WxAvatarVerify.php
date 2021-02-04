<?php


namespace App\QueueService\Common;


use App\Logic\WeWork\WxAvatarVerifyLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

class WxAvatarVerify
{
    /**
     * @AsyncQueueMessage(pool="wxAvatarVerify", maxAttempts=1)
     * @param int $corpId 企业ID
     * @param string $type 类型
     */
    public function handle(int $corpId, string $type = 'user'): void
    {
        $logic = make(WxAvatarVerifyLogic::class);
        $logic->handle($corpId, $type);
    }
}