<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\Utils\Media;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\RoomMessageBatchSend\Constants\Status;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendEmployeeContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendResultContract;

class SendJob extends Job
{
    /**
     * @var array
     */
    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $batchId = (int) $this->params['batchId'];
        $roomMessageBatchSendService = make(RoomMessageBatchSendContract::class);
        $roomMessageBatchSendEmployeeService = make(RoomMessageBatchSendEmployeeContract::class);
        $weWorkFactory = make(WeWorkFactory::class);
        $logger = make(StdoutLoggerInterface::class);

        Db::beginTransaction();
        try {
            $batch = $roomMessageBatchSendService->getRoomMessageBatchSendLockById($batchId);
            if (empty($batch) || $batch['sendStatus'] !== Status::NOT_SEND) {
                Db::commit();
                return;
            }

            $this->createSendTaskByParams($batch);
            $weWorkContactApp = $weWorkFactory->getContactApp((int) $batch['corpId']);

            $batchEmployees = $roomMessageBatchSendEmployeeService->getRoomMessageBatchSendEmployeesByBatchId($batchId, [], ['id', 'employee_id', 'wx_user_id']);
            $content = $this->formatContent((int) $batch['corpId'], $batch['content']);

            foreach ($batchEmployees as $employee) {
                $content['sender'] = $employee['wxUserId'];
                // 推送消息
                $batchResult = $weWorkContactApp->external_contact_message->submit($content);
                // 更新状态
                $roomMessageBatchSendEmployeeService->updateRoomMessageBatchSendEmployeeById($employee['id'], [
                    'errCode' => $batchResult['errcode'],
                    'errMsg' => $batchResult['errmsg'],
                    'msgId' => $batchResult['msgid'] ?? '',
                    'receiveStatus' => $batchResult['errcode'] === 0 ? 1 : 2, // 1-已接收 2-接收失败
                ]);
            }

            // 更新群发状态
            $roomMessageBatchSendService->updateRoomMessageBatchSendById($batchId, [
                'sendStatus' => Status::SEND_OK,
                'sendTime' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $logger->error(sprintf('%s [%s] %s', '客户群群发消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $logger->error($e->getTraceAsString());
        }
    }

    /**
     * 根据参数创建发送任务
     */
    private function createSendTaskByParams(array $sendData)
    {
        $workEmployeeService = make(WorkEmployeeContract::class);
        $roomMessageBatchSendService = make(RoomMessageBatchSendContract::class);
        $roomMessageBatchSendEmployeeService = make(RoomMessageBatchSendEmployeeContract::class);
        $roomMessageBatchSendResultService = make(RoomMessageBatchSendResultContract::class);
        $workRoomService = make(WorkRoomContract::class);
        $workContactRoomService = make(WorkContactRoomContract::class);

        $corpId = (int) $sendData['corpId'];
        $employeeIds = (array) $sendData['employeeIds'];
        $batchId = (int) $sendData['id'];

        // 获取用户成员
        $employees = $workEmployeeService->getWorkEmployeesByIdCorpIdStatus($corpId, $employeeIds, 1, ['id', 'wx_user_id']);

        $employeeTotal = count($employees);
        $roomTotal = 0;
        foreach ($employees as $employee) {
            // 获取成员客户
            $rooms = $workRoomService->getWorkRoomsByOwnerId($employee['id'], ['id', 'name', 'wx_chat_id', 'create_time']);
            $employeeRoomTotal = count($rooms);
            $roomTotal += $employeeRoomTotal;

            foreach ($rooms as $room) {
                $roomMessageBatchSendResultService->createRoomMessageBatchSendResult([
                    'batch_id' => $batchId,
                    'employee_id' => $employee['id'],
                    'room_id' => $room['id'],
                    'room_name' => $room['name'],
                    'room_create_time' => $room['createTime'],
                    'room_employee_num' => $workContactRoomService->countWorkContactRoomByRoomIds([$room['id']]),
                    'chat_id' => $room['wxChatId'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            // 成员
            $roomMessageBatchSendEmployeeService->createRoomMessageBatchSendEmployee([
                'batch_id' => $batchId,
                'employee_id' => $employee['id'],
                'wx_user_id' => $employee['wxUserId'],
                'send_room_total' => $employeeRoomTotal,
                'created_at' => date('Y-m-d H:i:s'),
                'last_sync_time' => date('Y-m-d H:i:s'),
            ]);
        }
        $roomMessageBatchSendService->updateRoomMessageBatchSendById($batchId, [
            'sendEmployeeTotal' => $employeeTotal,
            'notSendTotal' => $employeeTotal,
            'sendRoomTotal' => $roomTotal,
            'notReceivedTotal' => $roomTotal,
        ]);
    }

    private function formatContent(int $corpId, array $content)
    {
        if (count($content) == 0) {
            return $content;
        }

        $newContent = [
            'chat_type' => 'group',
        ];

        $media = make(Media::class);

        foreach ($content as $item) {
            if (empty($item['msgType'])) {
                continue;
            }

            switch ($item['msgType']) {
                case 'text':
                    $newContent['text']['content'] = $item['content'];
                    break;
                case 'image':
                    $mediaId = $media->uploadImage($corpId, $item['pic_url']);
                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => [
                            'media_id' => $mediaId,
                        ],
                    ];
                    break;
                case 'link':
                    if (! empty($item['pic_url'])) {
                        $item['picurl'] = $item['pic_url'];
                        unset($item['pic_url']);
                    }
                    unset($item['msgType']);
                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => $item,
                    ];
                    break;
                case 'miniprogram':
                    $item['pic_media_id'] = $media->uploadImage($corpId, $item['pic_url']);
                    unset($item['msgType'], $item['pic_url']);

                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => $item,
                    ];
                    break;
            }
        }
        return $newContent;
    }
}
