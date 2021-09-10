<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Job;

use Hyperf\AsyncQueue\Job;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;

class RoomSopJob extends Job
{
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
        /** @var WorkAgentContract $workAgentService */
        $workAgentService = make(WorkAgentContract::class);
        $messageRemind = make(MessageRemind::class);
        $agent = $workAgentService->getWorkAgentRemindByCorpId((int) $this->params['corpId'], ['id']);

        $url = Url::getSidebarBaseUrl() . '/roomSop?agentId=' . $agent['id'] . '&id=' . $this->params['roomSopLogId'];
        $text = "管理员 {$this->params['sopCreatorName']} 创建了群推送任务，提醒你给1个群聊发送消息\n<a href='{$url}'>点击查看详情</a>";

        $messageRemind->sendToEmployee(
            (int) $this->params['corpId'],
            $this->params['employeeWxId'],
            'text',
            $text
        );
    }
}
