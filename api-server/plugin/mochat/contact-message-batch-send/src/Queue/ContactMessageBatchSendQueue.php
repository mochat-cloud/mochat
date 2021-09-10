<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Queue;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use MoChat\Plugin\ContactMessageBatchSend\Job\SendJob;
use MoChat\Plugin\ContactMessageBatchSend\Job\SyncSendResultJob;

class ContactMessageBatchSendQueue
{
    /**
     * @var DriverInterface
     */
    private $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('contact');
    }

    /**
     * 客户群发异步队列，可以延时执行.
     *
     * @param array $params 数据
     * @param int $delay 延时时间 单位秒
     */
    public function push(array $params, int $delay = 0): bool
    {
        return $this->driver->push(new SendJob($params), $delay);
    }

    /**
     * 客户群发异步队列，可以延时执行.
     *
     * @param array $params 数据
     * @param int $delay 延时时间 单位秒
     */
    public function syncSendResult(array $params, int $delay = 0): bool
    {
        return $this->driver->push(new SyncSendResultJob($params), $delay);
    }
}
