<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Constants\MsgType;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkMessage\Contract\WorkMessageIdContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityRecordContract;

/**
 * 定时任务-群聊质检.
 *
 * Class RoomQualityLogic
 */
class RoomQualityLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

    /**
     * @Inject
     * @var WorkMessageIdContract
     */
    protected $workMessageIdService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var RoomQualityRecordContract
     */
    protected $roomQualityRecordService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var
     */
    protected $wx;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    private $workAgentService;

    public function handle(): void
    {
        try {
            $corps = $this->corpService->getCorps(['id', 'wx_corpid', 'contact_secret']);
            foreach ($corps as $corp) {
                $agent = $this->workAgentService->getWorkAgentByCorpIdClose($corp['id'], ['wx_agent_id']);
                if (empty($agent)) {
//                $this->logger->error(sprintf('群聊质检提醒失败::[%s]', '企业应用不存在'));
                    continue;
                }
                $this->roomQuality($corp, (int) $agent[0]['wxAgentId']);
            }
            $this->logger->info('群聊质检执行完成' . date('Y-m-d H:i:s', time()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '群聊质检执行失败', date('Y-m-d H:i:s'), $e->getMessage()));
        }
    }

    private function roomQuality(array $corp, int $agentId): array
    {
        ## 提醒方案
        $roomQuality = $this->roomQualityService->getRoomQualityByCorpIdStatus([$corp['id']], 1, ['id', 'rooms', 'quality_type', 'work_cycle', 'rule', 'white_list_status', 'keyword']);
        ## 1-群聊质检
        foreach ($roomQuality as $quality) {
            ## 质检时间
            $status = 0;
            if ($quality['qualityType'] === 1) {
                $status = 1;
            }
            if ($quality['qualityType'] === 2) {
                foreach (json_decode($quality['workCycle'], true, 512, JSON_THROW_ON_ERROR) as $cycle) {
                    $today = date('H:i');
                    if (in_array((int) date('w'), $cycle['week'], true) && $today > $cycle['start_time'] && $today < $cycle['end_time']) {
                        $status = 1;
                    }
                }
            }
            if ($status === 0) {
                continue;
            }
            ## 群聊
            $rooms = json_decode($quality['rooms'], true, 512, JSON_THROW_ON_ERROR);
            ## 2-规则
            foreach (json_decode($quality['rule'], true, 512, JSON_THROW_ON_ERROR) as $rule) {
                ## 时间：秒
                $time = 0;
                if ((int) $rule['time_type'] === 1) {
                    $time = (int) $rule['num'] * 60 * 1000;
                }
                if ((int) $rule['time_type'] === 2) {
                    $time = (int) $rule['num'] * 60 * 60 * 1000;
                }
                if ((int) $rule['time_type'] === 3) {
                    $time = (int) $rule['num'] * 60 * 60 * 24 * 1000;
                }
                ## 3-群聊
                foreach ($rooms as $room) {
                    $message = make(WorkMessageContract::class, [(int) $corp['id']])->getWorkMessagesRangeByIdCorpIdWxRoomIdMsgTime($corp['id'], $room['id'], ['id', 'from', 'tolist_type', 'msg_type', 'msg_time', 'content', 'room_id', 'status']);
                    ## 空消息
                    if (empty($message)) {
                        continue;
                    }
                    $message = (array) $message[0];
                    ## 不是客户发的消息
                    if (substr($message['from'], 0, 2) !== 'wm' && substr($message['from'], 0, 2) !== 'wo') {
                        continue;
                    }
                    ##已提醒过
                    if ($message['status'] === 1) {
                        continue;
                    }
                    ## 未到超时时间
                    if (time() * 1000 - $message['msg_time'] < $time) {
                        continue;
                    }
                    ## 质检白名单
                    $keyword_status = 0;
                    $allMsgType = array_merge(MsgType::$otherType, MsgType::$fixedType);
                    if ($quality['whiteListStatus'] === 1 && $message['msg_type'] === $allMsgType['text']) {
                        $content = json_decode($message['content'], true, 512, JSON_THROW_ON_ERROR);
                        if (isset($content['content'])) {
                            foreach ($quality['keyword'] as $k => $v) {
                                if (strpos($content['content'], $v)) {
                                    $keyword_status = 1;
                                }
                            }
                        }
                    }
                    if ($keyword_status == 1) {
                        continue;
                    }
                    ## 客户
                    $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $message['from'], ['name']);
                    if (empty($contact)) {
                        continue;
                    }
                    $name = empty($contact) ? '' : $contact['name'];
                    $messageRemind = make(MessageRemind::class);
                    ## 1：管理员。2：群主
                    if ((int) $rule['employee_type'] === 1) {
                        $employees = $this->workEmployeeService->getWorkEmployeesById($rule['employee'], ['wx_user_id']);
                        $content = "客户【{$name}】在群聊【{$room['name']}】中，发送信息未回复，请尽快回复";
                        if (! empty($employees)) {
                            $to = array_column($employees, 'wxUserId');
                            $messageRemind->sendToEmployee(
                                (int) $corp['id'],
                                $to,
                                'text',
                                $content
                            );
                        }

                        $params = [
                            'quality_id' => $quality['id'],
                            'message_id' => $message['id'],
                            'room_id' => $message['room_id'],
                            'type' => 1,
                            'content' => $content,
                            'corp_id' => $corp['id'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        $this->createRemindQuality($params);
                    }
                    if ((int) $rule['employee_type'] === 2) {
                        $roomInfo = $this->workRoomService->getWorkRoomById((int) $message['room_id'], ['owner_id', 'name']);
                        $employee = $this->workEmployeeService->getWorkEmployeeById((int) $roomInfo['ownerId'], ['wx_user_id']);
                        $content = "客户【{$name}】在群聊【{$roomInfo['name']}】中，发送信息未回复，请尽快回复";
                        $messageRemind->sendToEmployee(
                            (int) $corp['id'],
                            $employee['wxUserId'],
                            'text',
                            $content
                        );
                        $params = [
                            'quality_id' => $quality['id'],
                            'message_id' => $message['id'],
                            'room_id' => $message['room_id'],
                            'type' => 1,
                            'content' => $content,
                            'corp_id' => $corp['id'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        $this->createRemindQuality($params);
                    }
                }
            }
        }
        return [];
    }

    /**
     * @param $params
     */
    private function createRemindQuality($params): void
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建提醒记录
            $id = $this->roomQualityRecordService->createRoomQualityRecord($params);
            make(WorkMessageContract::class, [(int) $params['corp_id']])->updateWorkMessageById((int) $params['message_id'], ['status' => 1]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '群聊质检记录创建失败' . $params['message_id'], date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
    }
}
