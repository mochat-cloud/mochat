<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\RoomTagPull\Logic\RoomTagPullLogic;

/**
 * @Crontab(name="RoomTagPull", rule="*\/5 * * * * ", callback="execute", memo="这是一个标签建群的定时任务")
 */
class RoomTagPull
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        (new RoomTagPullLogic())->handle();
        $this->logger->info('标签建群获取企业群发成员执行结果' . date('Y-m-d H:i:s', time()));
    }
}
