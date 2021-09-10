<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use Hyperf\Session\Handler;

return [
    'handler' => Handler\RedisHandler::class,
    'options' => [
        'connection' => 'default',
        'path' => BASE_PATH . '/runtime/session',
        'gc_maxlifetime' => 1200,
        'session_name' => 'MOCHAT_SESSION_ID',
        'domain' => null,
        'cookie_lifetime' => 5 * 60 * 60,
    ],
];
