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

class IndexLogic
{

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
        ## 组织查询条件
        $where   = [];
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'id desc',
        ];
        ## 查询数据
        $res = $this->contactMessageBatchSend->getContactMessageBatchSendList($where, [
            'id',
            'send_way',
            'send_time',
            'send_total',
            'not_send_total',
            'received_total',
            'not_received_total',
            'definite_time',
            'send_time',
            'send_status',
            'created_at',
        ], $options);

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
        $data['list']              = $res['data'];

        return $data;
    }

}