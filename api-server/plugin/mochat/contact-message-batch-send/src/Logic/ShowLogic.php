<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;

class ShowLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     */
    public function handle(array $params, int $userId): array
    {
        $batch = $this->contactMessageBatchSend->getContactMessageBatchSendById((int) $params['batchId']);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }

        return [
            'id' => $batch['id'],
            'creator' => $batch['userName'],
            'createdAt' => $batch['createdAt'],
            'content' => $this->handleData($batch['content']),
            'sendTime' => $batch['sendTime'],
            'filterParams' => $batch['filterParams'],
            'filterParamsDetail' => $batch['filterParamsDetail'],
            'sendEmployeeTotal' => $batch['sendEmployeeTotal'],
            'sendContactTotal' => $batch['sendContactTotal'],
            'sendTotal' => $batch['sendTotal'],
            'receivedTotal' => $batch['receivedTotal'],
            'notSendTotal' => $batch['notSendTotal'],
            'notReceivedTotal' => $batch['notReceivedTotal'],
            'receiveLimitTotal' => $batch['receiveLimitTotal'],
            'notFriendTotal' => $batch['notFriendTotal'],
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
