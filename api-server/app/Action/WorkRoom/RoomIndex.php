<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoom;

use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 群聊列表下拉框.
 *
 * Class RoomIndex
 * @Controller
 */
class RoomIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    private $room;

    /**
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    private $contactRoom;

    /**
     * @RequestMapping(path="/workRoom/roomIndex", methods="get")
     */
    public function handle()
    {
        //接收参数
        $params['name']        = $this->request->input('name');
        $params['roomGroupId'] = $this->request->input('roomGroupId');
        //验证参数
        $this->validated($params);

        // 组织查询条件
        $where                                                          = [];
        empty($params['name']) || $where['name']                        = $params['name'];
        ! is_numeric($params['roomGroupId']) || $where['room_group_id'] = $params['roomGroupId'];

        //查询总群聊数
        $total = $this->room->countWorkRoomByCorpIds(user()['corpIds']);
        if (! empty($params['name'])) {
            $total = $this->room->countWorkRoomByCorpIdsName(user()['corpIds'], $params['name']);
        }

        //查询群聊信息
        $list = $this->room->getWorkRoomByCorpIdNameGroupId(user()['corpIds'], $where, ['id', 'name', 'room_max']);

        if (empty($list)) {
            return [
                'total' => 0,
                'list'  => [],
            ];
        }
        //查询当前群聊人数
        $roomNum                    = $this->contactRoom->countWorkContactRoomsByRoomIds(array_column($list, 'id'), 1);
        empty($roomNum) || $roomNum = array_column($roomNum, null, 'roomId');

        foreach ($list as &$raw) {
            $raw['roomId']     = $raw['id'];
            $raw['roomName']   = $raw['name'];
            $raw['currentNum'] = 0;

            if (isset($roomNum[$raw['id']])) {
                $raw['currentNum'] = $roomNum[$raw['id']]['total'];
            }
            unset($raw['id'], $raw['name']);
        }

        return [
            'total' => $total,
            'list'  => $list,
        ];
    }
}
