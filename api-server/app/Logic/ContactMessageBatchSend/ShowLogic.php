<?php

declare(strict_types=1);


/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Logic\ContactMessageBatchSend;

use App\Contract\ContactMessageBatchSendServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class ShowLogic
{
    /**
     * @Inject()
     * @var ContactMessageBatchSendServiceInterface
     */
    private $contactMessageBatchSend;

    public function handle(array $params, array $user): array
    {
        $batch = $this->contactMessageBatchSend->getContactMessageBatchSendById((int) $params['batchId']);
        if (!$batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $user['id']) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, "无操作权限");
        }

        return [
            'id'                 => $batch['id'],
            'creator'            => $batch['userName'],
            'createdAt'          => $batch['createdAt'],
            'content'            => $batch['content'],
            'sendTime'           => $batch['sendTime'],
            'filterParams'       => $batch['filterParams'],
            'filterParamsDetail' => $batch['filterParamsDetail'],
            'sendEmployeeTotal'  => $batch['sendEmployeeTotal'],
            'sendContactTotal'   => $batch['sendContactTotal'],
            'sendTotal'          => $batch['sendTotal'],
            'receivedTotal'      => $batch['receivedTotal'],
            'notSendTotal'       => $batch['notSendTotal'],
            'notReceivedTotal'   => $batch['notReceivedTotal'],
            'receiveLimitTotal'  => $batch['receiveLimitTotal'],
            'notFriendTotal'     => $batch['notFriendTotal'],
        ];
    }
}