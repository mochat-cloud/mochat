<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Task\SensitiveWordsMonitor;

use App\QueueService\SensitiveWordsMonitor\MonitorMessage as MonitorMessageQueue;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * @Crontab(name="monitorMessage", rule="*\/1 * * * *", callback="execute", singleton=true, memo="企业微信-敏感词监控")
 */
class MonitorMessage
{
    /**
     * @Inject
     * @var MonitorMessageQueue
     */
    private $monitorMessageQueue;

    public function execute(): void
    {
        ## 读取缓存中的企业会话信息
        $redisClient = make(Redis::class);
        ## 企业会话信息
        $wxMsgArr = [];
        for ($i = 0; $i < 5; ++$i) {
            $message = $redisClient->lPop('sensitiveMonitor');
            if (false === $message) {
                break;
            }
            $message = json_decode($message, true);
            if (isset($wxMsgArr[$message['corpId']])) {
                $wxMsgArr[$message['corpId']]['msgIds'] = array_merge($wxMsgArr[$message['corpId']]['msgIds'], $message['msgIds']);
            } else {
                $wxMsgArr[$message['corpId']] = [
                    'corpId' => $message['corpId'],
                    'msgIds' => $message['msgIds'],
                ];
            }
        }
        ## 异步处理
        empty($wxMsgArr) || $this->monitorMessageQueue->handle(array_values($wxMsgArr));
    }
}
