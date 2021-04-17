<?php

declare(strict_types=1);

/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Logic\RoomMessageBatchSend;

use App\Contract\RoomMessageBatchSendEmployeeServiceInterface;
use App\Contract\RoomMessageBatchSendResultServiceInterface;
use App\Contract\RoomMessageBatchSendServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\QueueService\RoomMessageBatchSend\StoreApply;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class StoreLogic
{

    /**
     * @Inject()
     * @var RoomMessageBatchSendServiceInterface
     */
    private $roomMessageBatchSend;

    /**
     * @Inject()
     * @var RoomMessageBatchSendEmployeeServiceInterface
     */
    private $roomMessageBatchSendEmployee;

    /**
     * @Inject()
     * @var RoomMessageBatchSendResultServiceInterface
     */
    private $roomMessageBatchSendResult;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $workEmployee;

    /**
     * @Inject()
     * @var WorkRoomServiceInterface
     */
    private $workRoom;

    /**
     * @Inject()
     * @var WorkContactRoomServiceInterface
     */
    private $workContactRoom;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param  array  $params
     * @param  array  $user
     * @return array
     */
    public function handle(array $params, array $user): bool
    {
        $corpId      = $user['corpIds'][0];
        $employeeIds = (array) $params['employeeIds'];

        ## 获取用户成员
        $employees = $this->workEmployee->getWorkEmployeesByIdCorpIdStatus($corpId, $employeeIds, 1, ['id', 'wx_user_id']);
        if (count($employees) !== count($employeeIds)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS);
        }

        ## 入库
        Db::beginTransaction();
        try {
            $batchContent = $params['content'];
            $batchId      = $this->roomMessageBatchSend->createRoomMessageBatchSend([
                'corp_id'       => $corpId,
                'user_id'       => $user['id'],
                'user_name'     => $user['name'] ?: $user['phone'],
                'batch_title'   => $params['batchTitle'],
                'content'       => json_encode($batchContent, JSON_UNESCAPED_UNICODE),
                'send_way'      => $params['sendWay'],
                'definite_time' => $params['definiteTime'],
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            $employeeTotal = 0;
            $roomTotal     = 0;
            foreach ($employees as $employee) {
                $employeeTotal++;
                ## 获取成员客户
                $rooms = $this->workRoom->getWorkRoomsByOwnerId($employee['id'], ['id', 'name', 'wx_chat_id', 'create_time']);
                var_dump($rooms);
                $roomTotal += count($rooms);
                ## 扩展多条消息
                foreach ($batchContent as $content) {
                    ## 客户群
                    $roomTotal = 0;
                    foreach ($rooms as $room) {
                        $this->roomMessageBatchSendResult->createRoomMessageBatchSendResult([
                            'batch_id'          => $batchId,
                            'employee_id'       => $employee['id'],
                            'room_id'           => $room['id'],
                            'room_name'         => $room['name'],
                            'room_create_time'  => $room['createTime'],
                            'room_employee_num' => $this->workContactRoom->countWorkContactRoomByRoomIds([$room['id']]),
                            'chat_id'           => $room['wxChatId'],
                            'created_at'        => date('Y-m-d H:i:s'),
                        ]);
                        $roomTotal++;
                    }
                    ## 成员
                    $this->roomMessageBatchSendEmployee->createRoomMessageBatchSendEmployee([
                        'batch_id'        => $batchId,
                        'employee_id'     => $employee['id'],
                        'wx_user_id'      => $employee['wxUserId'],
                        'send_room_total' => $roomTotal,
                        'content'         => json_encode($content, JSON_UNESCAPED_UNICODE),
                        'created_at'      => date('Y-m-d H:i:s'),
                        'last_sync_time'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            $this->roomMessageBatchSend->updateRoomMessageBatchSendById($batchId, [
                'sendEmployeeTotal' => $employeeTotal,
                'sendRoomTotal'     => $roomTotal,
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户群消息群发创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户群消息群发创建失败');
        }

        if ($params['sendWay'] == 1) {
            make(StoreApply::class)->handle($batchId);
        }

        return true;
    }
}