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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\ChannelCode\Logic\ChannelCodeLogic;

/**
 * @Crontab(name="channelCode", rule="*\/30 * * * * *", callback="execute", enable="isEnable", singleton=true, memo="渠道码-更新客户联系我方式")
 */
class ChannelCode
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        try {
            (new ChannelCodeLogic())->handle();
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '渠道码-更新客户联系我方式任务失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function isEnable(): bool
    {
        return true;
    }
}
