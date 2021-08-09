<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\ContactTransfer\Logic\TransferRoomLogic;

/**
 * @Crontab(name="TransferStateRefresh", rule="*\/5 * * * *", callback="execute", memo="分配状态更新定时器")
 */
class TransferStateRefresh
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        $this->logger->info('分配状态刷新' . date('Y-m-d H:i:s', time()));
        (new TransferRoomLogic())->setStateAll();
    }
}
