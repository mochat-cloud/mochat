<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\Task;

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\Logic\StoreLogic;

/**
 * @Crontab(name="pullAgent", rule="0 * * * *", callback="execute", singleton=true, memo="企业微信应用-同步应用")
 */
class SyncWorkAgent
{
    /**
     * @Inject
     * @var WorkAgentContract
     */
    protected $workAgentService;

    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(): void
    {
        $this->storeLogic->updateAgents();
    }
}
