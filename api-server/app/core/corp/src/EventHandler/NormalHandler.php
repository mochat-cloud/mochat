<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Corp\EventHandler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\QueueService\WeWorkCallback;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 默认回调处理.
 * @WeChatEventHandler
 */
class NormalHandler extends AbstractEventHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var WeWorkCallback
     */
    protected $weWorkCallback;

    public function process()
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($this->message)) {
            $this->logger->error('NormalHandler->process message不能为空');
            return 'success';
        }

        try {
            $this->weWorkCallback->handle($this->message);
            return 'success';
        } catch (\Throwable $e) {
            $this->logger->error('NormalHandler->process异常');
            return 'success';
        }
    }
}
