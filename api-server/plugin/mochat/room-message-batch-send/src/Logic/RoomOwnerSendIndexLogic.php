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
use MoChat\Plugin\RoomMessageBatchSend\Constants\Status;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendEmployeeContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendResultContract;

class RoomOwnerSendIndexLogic
{
    /**
     * @Inject
     * @var RoomMessageBatchSendEmployeeContract
     */
    private $roomMessageBatchSendEmployee;

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
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     * @return array 响应数组
     */
    public function handle(array $params, int $userId): array
    {
        $batchId = (int) $params['batchId'];
        $batch = $this->roomMessageBatchSend->getRoomMessageBatchSendById($batchId);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }
        ## 获取检索数据
        $res = $this->roomMessageBatchSendEmployee->getRoomMessageBatchSendEmployeesBySearch($params);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage' => $params['perPage'],
                'total' => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total'] = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];
        $data['list'] = [];
        foreach ($res['data'] as $item) {
            $item['sendSuccessTotal'] = $this->roomMessageBatchSendResult->getRoomMessageBatchSendResultCount([
                ['batch_id', '=', $batch['id']],
                ['employee_id', '=', $item['employeeId']],
                ['status', '=', Status::SEND_OK],
            ]);
            $data['list'][] = $item;
        }

        return $data;
    }
}
