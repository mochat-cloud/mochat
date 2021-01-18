<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis'  => [
            'pool' => 'default',
        ],
        'channel'        => 'queue',
        'timeout'        => 5,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 10,
        ],
    ],
    'employee' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel'        => 'employee.queue',
        'timeout'        => 2,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 2,
        ],
    ],
    'contact' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel'        => 'contact.queue',
        'timeout'        => 2,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 2,
        ],
    ],
    'room' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel'        => 'room.queue',
        'timeout'        => 2,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 2,
        ],
    ],
    'chat' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel'        => 'chat.queue',
        'timeout'        => 5,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 10,
        ],
    ],
    'coOSS' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel'        => 'coOss.queue',
        'timeout'        => 10,
        'retry_seconds'  => 5,
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 10,
        ],
    ],
];
