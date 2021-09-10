<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Utils\EasyWeChat\Work\ExternalContact;

class MessageClient extends \EasyWeChat\Work\ExternalContact\MessageClient
{
    /**
     * 获取群发成员发送任务列表.
     *
     * @see https://open.work.weixin.qq.com/api/doc/90000/90135/93338
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function getGroupTask(string $msgId, int $limit = 500, string $cursor = '')
    {
        return $this->httpPostJson('cgi-bin/externalcontact/get_groupmsg_task', [
            'msgid' => $msgId,
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }

    /**
     * 获取企业群发成员执行结果.
     *
     * @see https://open.work.weixin.qq.com/api/doc/90000/90135/93338
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function getGroupSendResult(string $msgId, string $userId, int $limit = 500, string $cursor = '')
    {
        return $this->httpPostJson('cgi-bin/externalcontact/get_groupmsg_send_result', [
            'msgid' => $msgId,
            'userId' => $userId,
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }
}
