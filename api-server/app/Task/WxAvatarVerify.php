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

use App\Contract\CorpServiceInterface;
use App\Logic\WeWork\WxAvatarVerifyLogic;
use Hyperf\Crontab\Annotation\Crontab;

/**
 * @Crontab(name="wxAvatarVerify", rule="0 1 * * *", callback="execute", singleton=true, memo="微信头像检测更新")
 */
class WxAvatarVerify
{
    public function execute(): void
    {
        $corpList = make(CorpServiceInterface::class)->getCorps(['id']);
        if (empty($corpList)) {
            return;
        }
        $logic = make(WxAvatarVerifyLogic::class);

        foreach ($corpList as $corp) {
            $logic->handle($corp['id']);
            $logic->handle($corp['id'], 'contact');
        }
    }
}
