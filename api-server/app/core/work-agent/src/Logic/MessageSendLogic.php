<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\Media;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 应用消息发送
 */
class MessageSendLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    protected $workAgentService;

    /**
     * @Inject
     * @var Media
     */
    protected $media;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function handle(array $params)
    {
        try {
            $agent = $this->workAgentService->getWorkAgentRemindByCorpId((int) $params['corpId'], ['id', 'wx_agent_id', 'wx_secret']);

            if (empty($agent)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '无可用的 agent');
            }

            $message = [
                'agentid' => $agent['wxAgentId'],
                'msgtype' => $params['msgType'],
                'safe' => isset($params['extra']['safe']) ? $params['extra']['safe'] : 0,
                'enable_id_trans' => isset($params['extra']['enable_id_trans']) ? $params['extra']['enable_id_trans'] : 0,
                'enable_duplicate_check' => isset($params['extra']['enable_duplicate_check']) ? $params['extra']['enable_duplicate_check'] : 0,
                'duplicate_check_interval' => isset($params['extra']['duplicate_check_interval']) ? $params['extra']['safe'] : 1800,
            ];

            if (isset($params['toUser'])) {
                if (is_array($params['toUser'])) {
                    $params['toUser'] = join('|', $params['toUser']);
                }
                $message['touser'] = $params['toUser'];
            } elseif (isset($params['toParty'])) {
                if (is_array($params['toParty'])) {
                    $params['toParty'] = join('|', $params['toParty']);
                }
                $message['toparty'] = $params['toParty'];
            } elseif (isset($params['toTag'])) {
                if (is_array($params['toTag'])) {
                    $params['toTag'] = join('|', $params['toTag']);
                }
                $message['totag'] = $params['toTag'];
            }

            $message = $this->addContent($params, $message);
            $res = $this->wxAgentApp($agent['id'])->message->send($message);
            if ($res['errcode'] !== 0) {
                $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '发送应用消息失败', date('Y-m-d H:i:s'), Json::encode($message), Json::encode($res)));
            }
            return $res;
        } catch (\Throwable $e) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '发送应用消息异常', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }

        return [];
    }

    protected function addContent(array $params, array $message)
    {
        if (is_string($params['content'])) {
            $message['text'] = ['content' => $params['content']];
        } else {
            $message[$params['msgType']] = $this->getMediaContent($params);
        }
        return $message;
    }

    protected function getMediaContent(array $params)
    {
        $content = $params['content'];
        if (isset($content['media_id']) && ! empty($content['media_id'])) {
            return $content;
        }

        $mediaId = $this->media->upload($params['corpId'], $params['msgType'], $content['path']);
        $content['media_id'] = $mediaId;
        unset($content['path']);
        return $content;
    }
}
