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
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
