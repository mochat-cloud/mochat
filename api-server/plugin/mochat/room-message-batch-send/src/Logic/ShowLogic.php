<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendResultContract;

class ShowLogic
{
    /**
     * @Inject
     * @var RoomMessageBatchSendContract
     */
    private $roomMessageBatchSend;

    /**
     * @Inject
     * @var RoomMessageBatchSendResultContract
     */
    private $roomMessageBatchSendResult;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    private $workRoom;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     */
    public function handle(array $params, int $userId): array
    {
        $batch = $this->roomMessageBatchSend->getRoomMessageBatchSendById((int) $params['batchId']);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }

        $roomIds = $this->roomMessageBatchSendResult->getRoomMessageBatchSendResultRoomIdsByBatchIds($batch['id']);
        $rooms = $this->workRoom->getWorkRoomsById(array_slice($roomIds, 0, 10), ['id', 'name']);

        return [
            'id' => $batch['id'],
            'batchTitle' => $batch['batchTitle'],
            'creator' => $batch['userName'],
            'createdAt' => $batch['createdAt'],
            'seedRooms' => $rooms,
            'content' => $this->handleData($batch['content']),
            'sendTime' => $batch['sendTime'],
            'sendEmployeeTotal' => $batch['sendEmployeeTotal'],
            'sendRoomTotal' => $batch['sendRoomTotal'],
            'sendTotal' => $batch['sendTotal'],
            'receivedTotal' => $batch['receivedTotal'],
            'notSendTotal' => $batch['notSendTotal'],
            'notReceivedTotal' => $batch['notReceivedTotal'],
        ];
    }

    protected function handleData($content): array
    {
        if (empty($content)) {
            return $content;
        }

        foreach ($content as $key => $value) {
            if ($value['msgType'] === 'text') {
                continue;
            }

            $content[$key]['pic_url'] = file_full_url($value['pic_url']);
        }
        return $content;
    }
}
