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
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;

class EmployeeSendIndexLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前用户ID
     * @return array 响应数组
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
        ## 查询数据
        $res = $this->contactMessageBatchSendEmployee->getContactMessageBatchSendEmployeesBySearch($params);
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
        $data['list'] = $res['data'];

        return $data;
    }
}
