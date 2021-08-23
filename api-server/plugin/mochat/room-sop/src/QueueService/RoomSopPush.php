<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\QueueService;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use MoChat\Plugin\RoomSop\Job\RoomSopJob;

class RoomSopPush
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 加好友回调中的 群sop异步队列.
     * @param array $params 数据
     * @param int $delay 延时时间 单位秒
     */
    public function push(array $params, int $delay = 0): bool
    {
        return $this->driver->push(new RoomSopJob($params), $delay);
    }
}
