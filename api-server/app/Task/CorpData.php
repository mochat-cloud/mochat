<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Task;

use App\Logic\CorpData\CorpDataLogic;
use Hyperf\Crontab\Annotation\Crontab;

/**
 * 10分钟执行一次
 * @Crontab(name="corpData", rule="*\/10 * * * *", callback="execute", singleton=true, memo="首页数据统计")
 */
class CorpData
{
    public function execute(): void
    {
        (new CorpDataLogic())->handle();
    }
}
