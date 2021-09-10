<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\QueueService;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use MoChat\Plugin\ContactSop\Job\ContactSopJob;

class ContactSopPush
{
    /**
     * 加好友回调中的 个人sop异步队列.
     * @param array $params 数据
     * @param int $delay 延时时间 单位秒
     */
    public function push(array $params, int $delay = 0): bool
    {
        $driverFactory = make(DriverFactory::class);
        $driver = $driverFactory->get('default');
        return $driver->push(new ContactSopJob($params), $delay);
    }
}
