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


use App\Contract\ContactMessageBatchSendResultServiceInterface;
use App\Contract\ContactMessageBatchSendServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class ContactReceiveIndexLogic
{

    /**
     * @Inject()
     * @var ContactMessageBatchSendResultServiceInterface
     */
    private $contactMessageBatchSendResult;

    /**
     * @Inject()
     * @var ContactMessageBatchSendServiceInterface
     */
    private $contactMessageBatchSend;

    /**
     * @param  array  $params  请求参数
     * @param  array  $user  当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, array $user): array
    {
        $batch = $this->contactMessageBatchSend->getContactMessageBatchSendById((int) $params['batchId']);
        if (!$batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, "未找到记录");
        }
        if ($batch['userId'] != $user['id']) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, "无操作权限");
        }
        ## 查询数据
        $res = $this->contactMessageBatchSendResult->getContactMessageBatchSendResultsBySearch($params);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];
        $data['list']              = [];
        foreach ($res['data'] as $item) {
            $item['sendTime'] = $item['sendTime'] > 0 ? date('Y-m-d H:i:s', $item['sendTime']) : '';
            $data['list'][]   = $item;
        }
        return $data;
    }

}