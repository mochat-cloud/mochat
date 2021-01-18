<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkRoom;

use App\Contract\CorpServiceInterface;
use App\Logic\WeWork\AppTrait;
use App\Logic\WorkRoom\WxSynLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 企业微信-创建|更新回调同步微信客户群以及群成员
 * Class UpdateCallBack.
 */
class UpdateCallback
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @AsyncQueueMessage(pool="room")
     * @param array $wxResponse 微信客户群创建|更新回调数据
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $wxResponse): void
    {
        $ecClient = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact;

        if (isset($wxResponse['ChatId']) && ! empty($wxResponse['ChatId'])) {
            ## 获取群聊详情
            $groupChat = $ecClient->getGroupChat($wxResponse['ChatId']);
            if ($groupChat['errcode'] != 0) {
                ## 记录错误日志
                $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户群详情错误', date('Y-m-d H:i:s'), $wxResponse['ChatId'], json_encode($groupChat)));
            } else {
                ## 获取客户群列表
                $chatListParams = [
                    'status_filter' => 0,
                    'owner_filter'  => [
                        'userid_list'  => [$groupChat['group_chat']['owner']],
                        'partyid_list' => [],
                    ],
                    'offset' => 0,
                    'limit'  => 100,
                ];
                $groupChatList = $ecClient->getGroupChats($chatListParams);
                if ($groupChatList['errcode'] != 0) {
                    ## 记录错误日志
                    $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户群列表错误', date('Y-m-d H:i:s'), json_encode($chatListParams), json_encode($groupChatList)));
                } else {
                    $groupChatList = array_column($groupChatList['group_chat_list'], 'status', 'chat_id');
                    if (isset($groupChatList[$wxResponse['ChatId']])) {
                        $groupChat['group_chat']['status'] = $groupChatList[$wxResponse['ChatId']];
                        ## 查询企业授信信息
                        $corpInfo = make(CorpServiceInterface::class)->getCorpsByWxCorpId($wxResponse['ToUserName'], ['id']);
                        if (isset($corpInfo['id'])) {
                            make(WxSynLogic::class)->handle([$groupChat['group_chat']], (int) $corpInfo['id'], 1);
                        }
                    }
                }
            }
        }
    }
}
