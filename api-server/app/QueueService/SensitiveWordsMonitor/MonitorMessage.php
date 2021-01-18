<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\SensitiveWordsMonitor;

use App\Constants\SensitiveWordsMonitor\ReceiverType;
use App\Constants\SensitiveWordsMonitor\Source;
use App\Constants\WorkMessage\MsgType;
use App\Contract\SensitiveWordMonitorServiceInterface;
use App\Contract\SensitiveWordServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkMessageServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * 敏感词监测-会话存档信息
 * Class MonitorMessage.
 */
class MonitorMessage
{
    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * @Inject
     * @var SensitiveWordMonitorServiceInterface
     */
    protected $monitorService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * 客户表.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $workContactService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @AsyncQueueMessage(pool="chat")
     * @param array $wxMsgArr 微信消息唯一标识数组按企业ID分类
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $wxMsgArr): void
    {
        ## 记录业务日志-1
        $this->logger->info(sprintf('%s [%s] %s', '会话信息监测敏感词-redis读取数据', date('Y-m-d H:i:s'), json_encode($wxMsgArr)));
        ## 校验参数
        if (empty($wxMsgArr)) {
            return;
        }
        ## 企业ID数组
        $corpIdArr = array_column($wxMsgArr, 'corpId');
        ## 查询企业全部敏感词【状态:开启】
        $sensitiveWords = $this->sensitiveWordService->getSensitiveWordsByCorpIdStatus($corpIdArr, 1, ['id', 'corp_id', 'name', 'employee_num', 'contact_num']);
        if (empty($sensitiveWords)) {
            return;
        }
        ## 记录业务日志-2
        $this->logger->info(sprintf('%s [%s] %s', '会话信息监测敏感词-敏感词信息', date('Y-m-d H:i:s'), json_encode($sensitiveWords)));
        ## 对敏感词按企业分类
        $sortWords = [];
        foreach ($sensitiveWords as $word) {
            $sortWords[$word['corpId']][] = $word;
        }
        ## 敏感词触发次数统计
        $updateWords = [];
        ## 敏感词触发记录
        $createMonitors = [];
        ## 查询消息数据列表-根据企业ID
        foreach ($wxMsgArr as $wxMsg) {
            $corpWords = isset($sortWords[$wxMsg['corpId']]) ? $sortWords[$wxMsg['corpId']] : [];
            if (empty($corpWords)) {
                continue;
            }
            $messageList = make(WorkMessageServiceInterface::class, [(int) $wxMsg['corpId']])->getWorkMessagesByMsgId($wxMsg['msgIds']);
            if (empty($messageList)) {
                continue;
            }
            ## 记录业务日志-3
            $this->logger->info(sprintf('%s [%s] %s', '会话信息监测敏感词-会话存档信息', date('Y-m-d H:i:s'), json_encode($messageList)));
            $matchRes       = $this->matchWords($messageList, $corpWords, $wxMsg['corpId']);
            $updateWords    = array_merge($updateWords, $matchRes['updateWords']);
            $createMonitors = array_merge($createMonitors, $matchRes['createMonitors']);
            ## 记录业务日志-4
            $this->logger->info(sprintf('%s [%s] %s', '会话信息监测敏感词-敏感词监控结果', date('Y-m-d H:i:s'), json_encode($createMonitors)));
        }
        ## 将数据入表
        $this->insertData($updateWords, $createMonitors);
    }

    /**
     * @param array $messageList 会话消息列表
     * @param array $corpWords 企业设置敏感词列表
     * @param int $corpId 企业ID
     * @return array 响应数组
     */
    private function matchWords(array $messageList, array $corpWords, int $corpId): array
    {
        ## 敏感词触发次数统计
        $updateWords = [];
        ## 敏感词触发记录
        $createMonitors = [];
        ## 获得信息发送方|接收方信息
        $participantIdArr = [];
        $wxRoomIdArr      = [];
        foreach ($messageList as $v) {
            is_array($v) || $v  = json_decode(json_encode($v), true);
            $participantIdArr[] = $v['from'];
            if (! empty($v['wx_room_id'])) {
                $wxRoomIdArr[] = $v['wx_room_id'];
                continue;
            }
            $tolist             = json_decode($v['tolist'], true);
            $participantIdArr[] = $tolist[0];
        }
        $userList    = $this->getEmployeeList($corpId, array_unique($participantIdArr));
        $contactList = $this->getContactList($corpId, array_unique($participantIdArr));
        ## 触发场景群聊
        $roomList = $this->getRoomList(array_unique($wxRoomIdArr));

        foreach ($messageList as $message) {
            is_array($message) || $message = json_decode(json_encode($message), true);
            ## 过滤-非文本消息
            if ($message['msg_type'] != MsgType::TEXT) {
                continue;
            }
            ## 过滤-非本公司员工|客户触发的信息
            if (! isset($userList[$message['from']]) && ! isset($contactList[$message['from']])) {
                continue;
            }
            ## 过滤内部群信息
            if (! empty($message['wx_room_id']) && ! isset($roomList[$message['wx_room_id']])) {
                continue;
            }
            $messageContent = json_decode($message['content'], true);
            if (! isset($messageContent['content']) || empty($messageContent['content'])) {
                continue;
            }
            ## 触发来源
            if (isset($userList[$message['from']])) {
                $triggerInfo = $userList[$message['from']];
            } else {
                $triggerInfo = $contactList[$message['from']];
            }
            ## 接收者类型
            $tolist = json_decode($message['tolist'], true);
            if (! empty($message['wx_room_id'])) {
                $receiverType = ReceiverType::ROOM;
                $receiverId   = $roomList[$message['wx_room_id']]['id'];
                $receiverName = $roomList[$message['wx_room_id']]['name'];
            } elseif (isset($userList[$tolist[0]])) {
                $receiverType = ReceiverType::EMPLOYEE;
                $receiverId   = $userList[$tolist[0]]['id'];
                $receiverName = $userList[$tolist[0]]['name'];
            } else {
                $receiverType = ReceiverType::CONTACT;
                $receiverId   = isset($contactList[$tolist[0]]) ? $contactList[$tolist[0]]['id'] : 0;
                $receiverName = isset($contactList[$tolist[0]]) ? $contactList[$tolist[0]]['name'] : $tolist[0];
            }
            $baseMonitor = [
                'corp_id'         => $corpId,
                'source'          => isset($userList[$message['from']]) ? Source::EMPLOYEE : Source::CONTACT,
                'trigger_id'      => $triggerInfo['id'],
                'trigger_name'    => $triggerInfo['name'],
                'receiver_type'   => $receiverType,
                'receiver_id'     => $receiverId,
                'receiver_name'   => $receiverName,
                'trigger_time'    => date('Y-m-d H:i:s', (int) round($message['msg_time'] / 1000)),
                'work_message_id' => $message['id'],
                'chat_content'    => $this->getChatContent($corpId, $message),
                'created_at'      => date('Y-m-d H:i:s'),
            ];
            foreach ($corpWords as $word) {
                ## 监测-是否触发敏感词
                if (stristr($messageContent['content'], $word['name']) === false) {
                    continue;
                }
                isset($updateWords[$word['id']]) || $updateWords[$word['id']] = [
                    'id'           => $word['id'],
                    'employee_num' => $word['employeeNum'],
                    'contact_num'  => $word['contactNum'],
                ];
                ## 触发来源
                if (isset($userList[$message['from']])) {
                    ++$updateWords[$word['id']]['employee_num'];
                } else {
                    ++$updateWords[$word['id']]['contact_num'];
                }
                $baseMonitor['sensitive_word_id']   = $word['id'];
                $baseMonitor['sensitive_word_name'] = $word['name'];
                $createMonitors[]                   = $baseMonitor;
            }
        }
        return [
            'updateWords'    => $updateWords,
            'createMonitors' => $createMonitors,
        ];
    }

    /**
     * @param int $corpId 企业ID
     * @param array $wxUserIdArr 微信用户ID
     * @return array 响应数组
     */
    private function getEmployeeList(int $corpId, array $wxUserIdArr): array
    {
        $list = $this->workEmployeeService->getWorkEmployeesByCorpIdsWxUserId([$corpId], $wxUserIdArr, ['id', 'wx_user_id', 'name']);
        return empty($list) ? [] : array_column($list, null, 'wxUserId');
    }

    /**
     * @param int $corpId 企业ID
     * @param array $wxExternalUserIdArr 微信外部联系人ID
     * @return array 响应数组
     */
    private function getContactList(int $corpId, array $wxExternalUserIdArr): array
    {
        $list = $this->workContactService->getWorkContactByCorpIdWxExternalUserIds($corpId, $wxExternalUserIdArr, ['id', 'wx_external_userid', 'name']);
        return empty($list) ? [] : array_column($list, null, 'wxExternalUserid');
    }

    /**
     * @param array $wxRoomIdArr 微信群聊ID
     * @return array 响应数组
     */
    private function getRoomList(array $wxRoomIdArr): array
    {
        $list = $this->workRoomService->getWorkRoomsByWxChatId($wxRoomIdArr, ['id', 'wx_chat_id', 'name']);
        return empty($list) ? [] : array_column($list, null, 'wxChatId');
    }

    /**
     * @param int $corpId 企业ID
     * @param array $message 单条会话信息
     * @return false|string 响应
     */
    private function getChatContent(int $corpId, array $message)
    {
        if (! empty($message['wx_room_id'])) {
            ## 查询此条信息前十条
            $beforeList = make(WorkMessageServiceInterface::class, [(int) $corpId])->getWorkMessagesRangeByCorpIdWxRoomId($corpId, $message['wx_room_id'], $message['id'], '<', 10, 'id desc', ['id', 'from', 'msg_type', 'content', 'msg_time']);
            ## 查询此条信息后十条
            $afterList = make(WorkMessageServiceInterface::class, [(int) $corpId])->getWorkMessagesRangeByCorpIdWxRoomId($corpId, $message['wx_room_id'], $message['id'], '>', 10, 'id asc', ['id', 'from', 'msg_type', 'content', 'msg_time']);
        } else {
            ## 查询此条信息前十条
            $beforeList = make(WorkMessageServiceInterface::class, [(int) $corpId])->getWorkMessagesRangeByCorpId($corpId, $message['from'], json_decode($message['tolist'], true)[0], $message['id'], '<', 10, 'id desc', ['id', 'from', 'msg_type', 'content', 'msg_time']);
            ## 查询此条信息后十条
            $afterList = make(WorkMessageServiceInterface::class, [(int) $corpId])->getWorkMessagesRangeByCorpId($corpId, $message['from'], json_decode($message['tolist'], true)[0], $message['id'], '>', 10, 'id asc', ['id', 'from', 'msg_type', 'content', 'msg_time']);
        }
        $beforeList = empty($beforeList) ? [] : array_map(function ($before) {
            return json_decode(json_encode($before), true);
        }, $beforeList);
        empty($beforeList) || array_multisort(array_column($beforeList, 'id'), SORT_ASC, $beforeList);
        $afterList = empty($afterList) ? [] : array_map(function ($after) {
            return json_decode(json_encode($after), true);
        }, $afterList);
        $list            = array_merge($beforeList, [$message], $afterList);
        $fromIdArr       = array_unique(array_column($list, 'from'));
        $fromUserList    = $this->getEmployeeList($corpId, $fromIdArr);
        $fromContactList = $this->getContactList($corpId, $fromIdArr);

        $data = array_map(function ($item) use ($message, $fromUserList, $fromContactList) {
            return [
                'isTrigger'  => $item['id'] == $message['id'] ? 1 : 0,
                'sender'     => $this->handleMessageSender($item, $fromUserList, $fromContactList),
                'sendTime'   => date('Y-m-d H:i:s', (int) round($item['msg_time'] / 1000)),
                'msgType'    => $item['msg_type'],
                'msgContent' => json_decode($item['content'], true),
            ];
        }, $list);

        return json_encode($data);
    }

    /**
     * @param array $message 会话消息
     * @param array $fromUserList 触发员工信息
     * @param array $fromContactList 触发客户信息
     */
    private function handleMessageSender(array $message, array $fromUserList, array $fromContactList): string
    {
        $sender = '系统';
        ## 发送人微信唯一标识前缀
        $prefixFrom = $message['from'][0] . $message['from'][1];
        ## 机器人
        if ($prefixFrom == 'we') {
            $data['name'] = '机器人';
        ## 客户
        } elseif ($prefixFrom == 'wo' || $prefixFrom == 'wm') {
            $sender = isset($fromContactList[$message['from']]) ? $fromContactList[$message['from']]['name'] : $message['from'];
        ## 员工
        } else {
            $sender = isset($fromUserList[$message['from']]) ? $fromUserList[$message['from']]['name'] : $message['from'];
        }
        return $sender;
    }

    /**
     * @param array $updateWords 敏感词统计数据
     * @param array $createMonitors 敏感词触发记录
     */
    private function insertData(array $updateWords, array $createMonitors)
    {
        //开启事务
        Db::beginTransaction();

        try {
            ## 更新触发敏感词统计信息
            if (! empty($updateWords)) {
                foreach ($updateWords as $word) {
                    $this->sensitiveWordService->updateSensitiveWordById((int) $word['id'], ['employee_num' => $word['employee_num'], 'contact_num' => $word['contact_num']]);
                }
            }
            ## 创建触发敏感词记录
            empty($createMonitors) || $this->monitorService->createSensitiveWordMonitors($createMonitors);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '会话信息监测敏感词触发入表失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
