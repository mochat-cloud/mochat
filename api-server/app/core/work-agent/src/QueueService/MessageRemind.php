<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\QueueService;

use MoChat\App\WorkAgent\Logic\MessageSendLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

/**
 * 消息提醒发送
 */
class MessageRemind
{
    /**
     * @AsyncQueueMessage(pool="remind")
     * 发送提醒给员工
     *
     * @param int|string $corpId 企业id
     * @param string|array $to 接收方
     * @param string $msgType 消息类型
     * @param string|array $content 消息内容
     * @param array $extra 扩展选项
     *
     * @return array
     */
    public function sendToEmployee($corpId, $to, string $msgType, $content, array $extra = []): array
    {
        return $this->send($corpId, 'user', $to, $msgType, $content, $extra);
    }

    /**
     * @AsyncQueueMessage(pool="remind")
     * 发送提醒给部门
     *
     * @param int|string $corpId 企业id
     * @param string|array $to 接收方
     * @param string $msgType 消息类型
     * @param string|array $content 消息内容
     * @param array $extra 扩展选项
     *
     * @return array
     */
    public function sendToParty($corpId, $to, string $msgType, $content, array $extra = []): array
    {
        return $this->send($corpId, 'party', $to, $msgType, $content, $extra);
    }

    /**
     * @AsyncQueueMessage(pool="remind")
     * 发送提醒给指定标签人员
     *
     * @param int|string $corpId 企业id
     * @param string|array $to 接收方
     * @param string $msgType 消息类型
     * @param string|array $content 消息内容
     * @param array $extra 扩展选项
     *
     * @return array
     */
    public function sendToTag($corpId, $to, string $msgType, $content, array $extra = []): array
    {
        return $this->send($corpId, 'tag', $to, $msgType, $content, $extra);
    }

    /**
     * 发送提醒
     *
     * @param int|string $corpId 企业id
     * @param string $toType 接收方类型
     * @param string|array $to 接收方
     * @param string $msgType 消息类型
     * @param string|array $content 消息内容
     * @param array $extra 扩展选项
     *
     * @return array
     */
    protected function send($corpId, $toType, $to, string $msgType, $content, array $extra = []): array
    {
        $params = [
            'corpId' => (int) $corpId,
            'msgType' => $msgType,
            'content' => $content,
            'extra' => $extra,
        ];

        if ($toType == 'user') {
            $params['toUser'] = $to;
        } else if ($toType == 'party') {
            $params['toParty'] = $to;
        } else if ($toType == 'tag') {
            $params['toTag'] = $to;
        }

        $messageSendLogic = make(MessageSendLogic::class);
        return $messageSendLogic->handle($params);
    }
}