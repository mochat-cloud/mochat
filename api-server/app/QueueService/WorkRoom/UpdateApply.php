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
 * 企业微信-主动同步微信客户群以及群成员
 * Class UpdateApply.
 */
class UpdateApply
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @AsyncQueueMessage(pool="room")
     * @param int $corpId 企业授信ID
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(int $corpId): void
    {
        $this->logger->error(sprintf('%s [%s]', '同步客户群测试', date('Y-m-d H:i:s')));
        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        ## 获取客户群列表
        $chatListParams = [
            'status_filter' => 0,
            'owner_filter'  => [
                'userid_list'  => [],
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
            $wxRoomList = array_map(function ($chat) use ($ecClient) {
                ## 获取群聊详情
                $groupChat = $ecClient->getGroupChat($chat['chat_id']);
                if ($groupChat['errcode'] != 0) {
                    ## 记录错误日志
                    $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户群详情错误', date('Y-m-d H:i:s'), $chat['chat_id'], json_encode($groupChat)));
                } else {
                    $groupChat['group_chat']['status'] = $chat['status'];
                    return $groupChat['group_chat'];
                }
            }, $groupChatList['group_chat_list']);

            empty($wxRoomList) || make(WxSynLogic::class)->handle($wxRoomList, $corpId);
        }
    }
}
