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
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;

class IndexLogic
{
    /**
     * @Inject
     * @var RoomMessageBatchSendContract
     */
    private $roomMessageBatchSend;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, int $userId): array
    {
        ## 组织查询条件
        $where = [
            ['user_id', '=', $userId],
        ];

        if (isset($params['batchTitle']) && ! empty($params['batchTitle'])) {
            $where[] = ['batch_title', 'like', '%' . $params['batchTitle'] . '%'];
        }

        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'id desc',
        ];
        ## 查询数据
        $res = $this->roomMessageBatchSend->getRoomMessageBatchSendList($where, [
            'id',
            'batch_title',
            'send_way',
            'content',
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
        foreach ($res['data'] as $key => $val) {
            foreach ($val['content'] as $k => $v) {
                if (isset($v['local_url'])) {
                    $res['data'][$key]['content'][$k]['local_url'] = file_full_url($v['local_url']);
                }
            }
        }
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];
        $data['list']              = $res['data'];

        return $data;
    }
}
