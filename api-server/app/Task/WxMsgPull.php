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

use App\Contract\WorkMessageConfigServiceInterface;
use App\QueueService\WorkMessage\StoreApply;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="wxMsgPull", rule="*\/5 * * * *", callback="execute", singleton=true, memo="企业微信-会话内容存档-聊天内容拉取")
 */
class WxMsgPull
{
    /**
     * @Inject
     * @var WorkMessageConfigServiceInterface
     */
    private $corConfigClient;

    /**
     * @Inject
     * @var StoreApply
     */
    private $storeApply;

    public function execute(): void
    {
        // 循环企业，投递到队列任务
        $corpConfig = $this->corConfigClient->getWorkMessageConfigsByDoneStatus(['id', 'corp_id']);

        foreach ($corpConfig as $corp) {
            $this->storeApply->handle($corp['corpId']);
        }
    }
}
