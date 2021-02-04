<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    'mode'    => SWOOLE_PROCESS,
    'servers' => [
        [
            'name'      => 'http',
            'type'      => Server::SERVER_HTTP,
            'host'      => '0.0.0.0',
            'port'      => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        'enable_coroutine'      => true,
        'worker_num'            => swoole_cpu_num(),
        'pid_file'              => BASE_PATH . '/runtime/hyperf.pid',
        'open_tcp_nodelay'      => true,
        'max_coroutine'         => 100000,
        'open_http2_protocol'   => true,
        'max_request'           => 100000,
        'socket_buffer_size'    => 2 * 1024 * 1024,
        'buffer_output_size'    => 2 * 1024 * 1024,
        'package_max_length'    => 10 * 1024 * 1024,
        'log_file'              => env('LOG_FILE', BASE_PATH . '/runtime/swoole.log'),
        'document_root'         => BASE_PATH . '/runtime',
        'enable_static_handler' => true,
    ],
    'callbacks' => [
        SwooleEvent::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        SwooleEvent::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        SwooleEvent::ON_WORKER_EXIT  => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
    ],
];
