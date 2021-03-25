<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Task\WorkAgent;

use App\Contract\WorkAgentServiceInterface;
use App\Logic\Agent\StoreLogic;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="pullAgent", rule="* *\/6 * * *", callback="execute", singleton=true, memo="企业微信应用-同步应用")
 */
class SynWorkAgent
{
    /**
     * @Inject
     * @var WorkAgentServiceInterface
     */
    protected $workAgentClient;

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
