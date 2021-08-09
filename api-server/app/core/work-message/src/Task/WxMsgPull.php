<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\App\WorkMessage\QueueService\StoreApply;

/**
 * @Crontab(name="wxMsgPull", rule="*\/5 * * * *", callback="execute", singleton=true, memo="企业微信-会话内容存档-聊天内容拉取")
 */
class WxMsgPull
{
    /**
     * @Inject
     * @var WorkMessageConfigContract
     */
    private $corConfigClient;

    /**
     * @Inject
     * @var StoreApply
     */
    private $storeApply;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        // 循环企业，投递到队列任务
        $corpConfig = $this->corConfigClient->getWorkMessageConfigsByDoneStatus(['id', 'corp_id']);
        $this->logger->info('会话内容存档' . date('Y-m-d H:i:s', time()));
        foreach ($corpConfig as $corp) {
            $this->storeApply->handle($corp['corpId']);
        }
    }
}
