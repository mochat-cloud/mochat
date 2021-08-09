<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

//  /var/www-log/mochat
$logDir = rtrim(env('WWW_LOG_DIR', BASE_PATH . '/runtime'), '/') . '/logs/';

$formatter = [
    'class'       => LogstashFormatter::class,
    'constructor' => [
        'applicationName' => 'mochat',
    ],
];

$handlers = [
    'default' => [
        'handlers' => [
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/info.log',
                    'level'    => Logger::INFO,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/error.log',
                    'level'    => Logger::ERROR,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/warning.log',
                    'level'    => Logger::WARNING,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/critical.log',
                    'level'    => Logger::CRITICAL,
                ],
                'formatter' => $formatter,
            ],
        ],
    ],
];

$appEnv = env('APP_ENV', 'production');
if ($appEnv !== 'production') {
    $handlers['default']['handlers'][] = [
        'class'       => RotatingFileHandler::class,
        'constructor' => [
            'filename' => $logDir . '/debug.log',
            'level'    => Logger::DEBUG,
        ],
        'formatter' => $formatter,
    ];
}

return $handlers;
