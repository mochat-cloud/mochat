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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Queue\RoomMessageBatchSendQueue;

class StoreLogic
{
    /**
     * @Inject
     * @var RoomMessageBatchSendContract
     */
    private $roomMessageBatchSend;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployee;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Inject
     * @var RoomMessageBatchSendQueue
     */
    private $roomMessageBatchSendQueue;

    public function handle(array $params, array $user): bool
    {
        $corpId = $user['corpIds'][0];
        $employeeIds = (array) $params['employeeIds'];

        // 获取用户成员
        $employees = $this->workEmployee->getWorkEmployeesByIdCorpIdStatus($corpId, $employeeIds, 1, ['id', 'wx_user_id']);
        if (count($employees) !== count($employeeIds)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS);
        }

        // 入库
        Db::beginTransaction();
        try {
            $employeeIds = ! empty($params['employeeIds']) ? json_encode($params['employeeIds'], JSON_UNESCAPED_UNICODE) : '[]';
            $batchId = $this->roomMessageBatchSend->createRoomMessageBatchSend([
                'corp_id' => $corpId,
                'user_id' => $user['id'],
                'user_name' => $user['name'] ?: $user['phone'],
                'employee_ids' => $employeeIds,
                'batch_title' => $params['batchTitle'],
                'content' => json_encode($params['content'], JSON_UNESCAPED_UNICODE),
                'send_way' => $params['sendWay'],
                'definite_time' => $params['definiteTime'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $delay = 0;
            if ((int) $params['sendWay'] === 2) {
                $delay = strtotime($params['definiteTime']) - time();
                $delay = $delay < 0 ? 0 : $delay;
            }

            $this->roomMessageBatchSendQueue->push(['batchId' => $batchId], $delay);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户群消息群发创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户群消息群发创建失败');
        }

        return true;
    }
}
