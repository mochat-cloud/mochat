<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\RoomQuality\Logic\RoomQualityLogic;

/**
 * @Crontab(name="RoomQuality", rule="*\/5 * * * * ", callback="execute", memo="这是一个群聊质检的定时任务")
 */
class RoomQuality
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        (new RoomQualityLogic())->handle();
        $this->logger->info('群聊质检' . date('Y-m-d H:i:s', time()));
    }
}
