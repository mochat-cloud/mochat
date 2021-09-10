<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomRemind\Logic;

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
use MoChat\Plugin\RoomRemind\Contract\RoomRemindContract;
use MoChat\Plugin\RoomRemind\Contract\RoomRemindRecordContract;

/**
 * 群消息提醒.
 *
 * Class RoomRemindLogic
 */
class RoomRemindLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomRemindContract
     */
    protected $roomRemindService;

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
     * @var RoomRemindRecordContract
     */
    protected $roomRemindRecordService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

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
                    continue;
                }
                $this->roomRemind($corp, (int) $agent[0]['wxAgentId']);
            }
            $this->logger->info('群消息提醒执行完成' . date('Y-m-d H:i:s', time()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '群消息提醒执行失败', date('Y-m-d H:i:s'), $e->getMessage()));
        }
    }

    private function roomRemind(array $corp, int $agentId): array
    {
        ## 起始索引
        $startId = $this->workMessageIdService->getWorkMessageLastIdByCorpIdType($corp['id'], 1);
        $endId = $startId + 5000;
        $maxId = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageMaxId();
        $endId = $maxId >= $endId ? $endId : $maxId;
        ## 提醒方案
        $roomRemind = $this->roomRemindService->getRoomRemindByCorpIdStatus([$corp['id']], 1, ['id', 'rooms', 'is_qrcode', 'is_link', 'is_miniprogram', 'is_card', 'is_keyword', 'keyword']);
        ## 消息类型
        $allMsgType = array_merge(MsgType::$otherType, MsgType::$fixedType);
        $messageRemind = make(MessageRemind::class);
        foreach ($roomRemind as $k => $v) {
            $rooms = json_decode($v['rooms'], true, 512, JSON_THROW_ON_ERROR);
            $roomArr = array_column($rooms, 'id');
            ## 二维码
            if ($v['isQrcode'] === 1) {
                $message = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageByCorpIdRoomIdMsgType($corp['id'], $roomArr, $allMsgType['weapp'], [$startId, $endId], ['id', 'from', 'room_id']);

                foreach ($message as $item) {
                    $item = (array) $item;
                    $room = $this->workRoomService->getWorkRoomById((int) $item['room_id'], ['owner_id', 'name']);
                    $employee = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['wx_user_id']);
                    $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $item['from'], ['name']);
                    $name = empty($contact) ? '' : $contact['name'];
                    $content = "客户【{$name}】在群聊【{$room['name']}】中，发送了带二维码的图片";
                    $messageRemind->sendToEmployee(
                        (int) $corp['id'],
                        $employee['wxUserId'],
                        'text',
                        $content
                    );
                    $params = [
                        'remind_id' => $v['id'],
                        'message_id' => $item['id'],
                        'room_id' => $item['room_id'],
                        'type' => 1,
                        'content' => $content,
                        'keyword' => '',
                        'corp_id' => $corp['id'],
                    ];
                    $this->createRemindRecord($params);
                }
            }
            ## 链接
            if ($v['isLink'] === 1) {
                $message = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageByCorpIdRoomIdMsgType($corp['id'], $roomArr, $allMsgType['link'], [$startId, $endId], ['id', 'from', 'room_id']);
                foreach ($message as $item) {
                    $item = (array) $item;
                    $room = $this->workRoomService->getWorkRoomById((int) $item['room_id'], ['owner_id', 'name']);
                    $employee = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['wx_user_id']);
                    $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $item['from'], ['name']);
                    $name = empty($contact) ? '' : $contact['name'];
                    $content = "客户【{$contact['name']}】在群聊【{$room['name']}】中，发送了链接";
                    $messageRemind->sendToEmployee(
                        (int) $corp['id'],
                        $employee['wxUserId'],
                        'text',
                        $content
                    );
                    $params = [
                        'remind_id' => $v['id'],
                        'message_id' => $item['id'],
                        'room_id' => $item['room_id'],
                        'type' => 2,
                        'content' => $content,
                        'keyword' => '',
                        'corp_id' => $corp['id'],
                    ];
                    $this->createRemindRecord($params);
                }
            }
            ## 小程序
            if ($v['isMiniprogram'] === 1) {
                $message = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageByCorpIdRoomIdMsgType($corp['id'], $roomArr, $allMsgType['weapp'], [$startId, $endId], ['id', 'from', 'room_id']);
                foreach ($message as $item) {
                    $item = (array) $item;
                    $room = $this->workRoomService->getWorkRoomById((int) $item['room_id'], ['owner_id', 'name']);
                    $employee = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['wx_user_id']);
                    $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $item['from'], ['name']);
                    $name = empty($contact) ? '' : $contact['name'];
                    $content = "客户【{$name}】在群聊【{$room['name']}】中，发送了小程序";
                    $messageRemind->sendToEmployee(
                        (int) $corp['id'],
                        $employee['wxUserId'],
                        'text',
                        $content
                    );
                    $params = [
                        'remind_id' => $v['id'],
                        'message_id' => $item['id'],
                        'room_id' => $item['room_id'],
                        'type' => 3,
                        'content' => $content,
                        'keyword' => '',
                        'corp_id' => $corp['id'],
                    ];
                    $this->createRemindRecord($params);
                }
            }
            ## 名片
            if ($v['isCard'] === 1) {
                $message = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageByCorpIdRoomIdMsgType($corp['id'], $roomArr, $allMsgType['card'], [$startId, $endId], ['id', 'from', 'room_id']);
                foreach ($message as $item) {
                    $item = (array) $item;
                    $room = $this->workRoomService->getWorkRoomById((int) $item['room_id'], ['owner_id', 'name']);
                    $employee = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['wx_user_id']);
                    $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $item['from'], ['name']);
                    $name = empty($contact) ? '' : $contact['name'];
                    $content = "客户【{$name}】在群聊【{$room['name']}】中，发送了名片";
                    $messageRemind->sendToEmployee(
                        (int) $corp['id'],
                        $employee['wxUserId'],
                        'text',
                        $content
                    );
                    $params = [
                        'remind_id' => $v['id'],
                        'message_id' => $item['id'],
                        'room_id' => $item['room_id'],
                        'type' => 4,
                        'content' => $content,
                        'keyword' => '',
                        'corp_id' => $corp['id'],
                    ];
                    $this->createRemindRecord($params);
                }
            }
            ## 关键词
            if ($v['isKeyword'] === 1) {
                foreach (explode(',', $v['keyword']) as $keyword) {
                    $message = make(WorkMessageContract::class, [$corp['id']])->getWorkMessageByCorpIdRoomIdMsgTypeKeyword($corp['id'], $roomArr, $allMsgType['text'], $keyword, [$startId, $endId], ['id', 'from', 'room_id']);
                    $this->logger->info('群消息提醒-消息' . json_encode($message, JSON_THROW_ON_ERROR));
                    foreach ($message as $item) {
                        $item = (array) $item;
                        $room = $this->workRoomService->getWorkRoomById((int) $item['room_id'], ['owner_id', 'name']);
                        $employee = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['wx_user_id']);
                        $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $item['from'], ['name']);
                        $name = empty($contact) ? '' : $contact['name'];
                        $content = "客户【{$name}】在群聊【{$room['name']}】中，发送了关键词：{$keyword}";
                        $messageRemind->sendToEmployee(
                            (int) $corp['id'],
                            $employee['wxUserId'],
                            'text',
                            $content
                        );
                        $params = [
                            'remind_id' => $v['id'],
                            'message_id' => $item['id'],
                            'room_id' => $item['room_id'],
                            'type' => 5,
                            'content' => $content,
                            'keyword' => $keyword,
                            'corp_id' => $corp['id'],
                        ];
                        $this->createRemindRecord($params);
                    }
                }
            }
        }
        $info = $this->workMessageIdService->getWorkMessageIdByCorpIdType($corp['id'], 1, ['id']);
        if (empty($info)) {
            $this->workMessageIdService->createWorkMessageId(['corp_id' => $corp['id'], 'type' => 1, 'last_id' => $endId, 'created_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->workMessageIdService->updateWorkMessageIdById((int) $info['id'], ['last_id' => $endId]);
        }
        return [$endId];
    }

    /**
     * @param $params
     */
    private function createRemindRecord($params): void
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建提醒记录
            $id = $this->roomRemindRecordService->createRoomRemindRecord($params);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户群提醒记录创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
    }
}
