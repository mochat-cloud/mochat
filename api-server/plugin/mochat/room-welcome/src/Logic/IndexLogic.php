<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomWelcome\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;

/**
 * 入群欢迎语-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var RoomWelcomeContract
     */
    protected $roomWelcomeService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($params);
        ## 查询数据
        return $this->getroomWelcomesList($params);
    }

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $where = [];
        empty($params['text']) || $where[] = ['msg_text', 'LIKE', '%' . $params['text'] . '%'];
        $user = user();
        $where['corp_id'] = $user['corpIds'][0];
        if ($user['isSuperAdmin'] === 0) {
            $where['create_user_id'] = $user['id'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取欢迎语列表.
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getroomWelcomesList(array $params): array
    {
        $columns = ['id', 'complex_type', 'msg_text', 'msg_complex', 'create_user_id', 'created_at'];
        $roomWelcomeList = $this->roomWelcomeService->getRoomWelcomeList($params['where'], $columns, $params['options']);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];
        return empty($roomWelcomeList['data']) ? $data : $this->handleData($roomWelcomeList);
    }

    /**
     * 数据处理.
     * @param array $roomWelcomeList 欢迎语列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $roomWelcomeList): array
    {
        $list = [];
        foreach ($roomWelcomeList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $msgComplex = json_decode($val['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            if (! empty($msgComplex['pic'])) {
                $msgComplex['pic'] = file_full_url($msgComplex['pic']);
            }
            $list[$key] = [
                'id' => $val['id'],
                'msg_text' => $val['msgText'],
                'complex_type' => $val['complexType'],
                'msg_complex' => json_encode($msgComplex, JSON_THROW_ON_ERROR),
                'create_user' => isset($username['name']) ? $username['name'] : '',
                'create_time' => $val['createdAt'],
            ];
        }
        $data['page']['total'] = $roomWelcomeList['total'];
        $data['page']['totalPage'] = $roomWelcomeList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
