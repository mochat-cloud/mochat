<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Job;

use Hyperf\AsyncQueue\Job;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;

class ContactSopJob extends Job
{
    use AppTrait;

    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 执行.
     */
    public function handle()
    {
        $workAgentService = make(WorkAgentContract::class);
        $agent = $workAgentService->getWorkAgentRemindByCorpId((int) $this->params['corpId'], ['id']);
        $timestamp = time();
        $nowTime = date('H:i', $timestamp);
        $nowDate = date('Y-m-d', $timestamp);

        $url = Url::getSidebarBaseUrl() . '/contactSop?agentId=' . $agent['id'] . '&id=' . $this->params['contactSopLogId'];
        $text = "【个人SOP】\n" .
            "提醒时间: 今天{$nowTime}({$nowDate})\n" .
            "跟进客户: {$this->params['contactName']}\n" .
            "提醒内容: 个人SOP「 {$this->params['sopName']} 」 回复规则\n" .
            "<a href='{$url}'>点击查看详情</a>";
        $messageRemind = make(MessageRemind::class);
        $messageRemind->sendToEmployee(
            (int) $this->params['corpId'],
            $this->params['employeeWxId'],
            'text',
            $text
        );
    }
}
