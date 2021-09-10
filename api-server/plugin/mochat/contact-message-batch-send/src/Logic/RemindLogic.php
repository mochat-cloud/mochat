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
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;

class RemindLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployee;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     */
    public function handle(array $params, int $userId): bool
    {
        $batch = $this->contactMessageBatchSend->getContactMessageBatchSendById((int) $params['batchId']);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }
        $sendTime = $batch['createdAt'];
        if (! empty($params['batchEmployId'])) {
            $employee = $this->workEmployee->getWorkEmployeeById((int) $params['batchEmployId'], ['wx_user_id']);
            $this->sendMessage($sendTime, $batch['corpId'], $employee['wxUserId']);
            return true;
        }
        $employees = $this->contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeesByBatchId((int) $params['batchId'], [], ['wx_user_id']);
        foreach ($employees as $item) {
            $this->sendMessage($sendTime, $batch['corpId'], $item['wxUserId']);
        }
        return true;
    }

    protected function sendMessage($sendTime, $corpId, $wxUserId)
    {
        $text = "【任务提醒】有新的任务啦！\n" .
            "任务类型：客户群发任务\n" .
            "创建时间：{$sendTime}\n" .
            "可前往【客户联系】中确认发送，记得及时完成哦\n";
        $messageRemind = make(MessageRemind::class);
        $messageRemind->sendToEmployee(
            $corpId,
            $wxUserId,
            'text',
            $text
        );
    }
}
