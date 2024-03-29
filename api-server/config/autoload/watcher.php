<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use Hyperf\Watcher\Driver\ScanFileDriver;

$dir = ['app', 'plugin', 'config'];

return [
    'driver' => ScanFileDriver::class,
    'bin' => 'php',
    'watch' => [
        'dir' => $dir,
        'file' => ['.env'],
        'scan_interval' => 2000,
    ],
];
