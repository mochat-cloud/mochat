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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;

class RemindLogic
{
    /**
     * @Inject
     * @var RoomMessageBatchSendContract
     */
    private $roomMessageBatchSend;

    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     */
    public function handle(array $params, int $userId): bool
    {
        $batch = $this->roomMessageBatchSend->getRoomMessageBatchSendById((int) $params['batchId']);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }
//        $employees = $this->roomMessageBatchSend->get((int) $params['batchId'], (array) $params['batchEmployIds']);
        // 发送提醒消息

        return true;
    }
}
