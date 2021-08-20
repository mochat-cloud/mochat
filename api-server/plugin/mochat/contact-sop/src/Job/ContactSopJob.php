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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;

class ContactSopJob extends Job
{
    use AppTrait;

    public $params;

    public function __construct($params)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    /**
     * 执行.
     */
    public function handle()
    {
        /** @var CorpContract $corpService */
        $corpService = make(CorpContract::class);
        /** @var WorkAgentContract $workAgentService */
        $workAgentService = make(WorkAgentContract::class);
        $logger =  make(StdoutLoggerInterface::class);
        $info      = $corpService->getCorpById($this->params['corpId']);
        $timestamp = time();
        $nowTime   = date('H:i', $timestamp);
        $nowDate   = date('Y-m-d', $timestamp);

        $url  = Url::getSidebarBaseUrl() . '/personalSop?id=' . $this->params['contactSopLogId'];
        $text = "【个人SOP】\n" .
            "提醒时间: 今天{$nowTime}({$nowDate})\n" .
            "跟进客户: {$this->params['contactName']}\n" .
            "提醒内容: 个人SOP「 {$this->params['sopName']} 」 回复规则\n" .
            "<a href='{$url}'>点击查看详情</a>";
        $agent = $workAgentService->getWorkAgentByCorpIdClose($info['id'], ['wx_agent_id']);
        if (empty($agent)) {
            $logger->error(sprintf('个人SOP提醒失败::[%s]', '企业应用不存在'));
        } else {
            ##EasyWeChat发送应用消息
            $res = $this->wxApp($this->params['corpId'], 'contact')->message->send([
                'touser'  => $this->params['employeeWxId'],
                'msgtype' => 'text',
                'agentid' => (int) $agent[0]['wxAgentId'],
                'text'    => [
                    'content' => $text,
                ], ]);
            if ($res['errcode'] !== 0) {
                $logger->error(sprintf('个人SOP提醒失败::[%s]', $agent[0]['wxAgentId'] . json_encode($res, JSON_THROW_ON_ERROR)));
            }
        }
    }
}
