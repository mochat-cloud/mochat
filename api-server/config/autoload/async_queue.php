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
        'redis' => [
            'pool' => 'default',
        ],
        'channel' => 'queue',
        'timeout' => 5,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'employee' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'employee.queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 60 * 3,
        'processes' => 1,
        'concurrent' => [
            'limit' => 20,
        ],
    ],
    'contact' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'contact.queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 2,
        ],
    ],
    'welcome' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'welcome.queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'room' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'room.queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 2,
        ],
    ],
    'chat' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'chat.queue',
        'timeout' => 5,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'file' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'file.queue',
        'timeout' => 10,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'callback' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'callback.queue',
        'timeout' => 10,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'remind' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'remind.queue',
        'timeout' => 10,
        'retry_seconds' => 5,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 10,
        ],
    ],
    'message_media' => [
        'driver' => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'message_media.queue',
        'timeout' => 10,
        'retry_seconds' => 5,
        'handle_timeout' => 300,
        'processes' => 1,
        'concurrent' => [
            'limit' => 50,
        ],
    ],
];
