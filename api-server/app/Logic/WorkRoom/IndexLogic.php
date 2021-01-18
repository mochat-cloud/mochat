<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkRoom;

use App\Constants\WorkContactRoom\Status as WorkContactRoomStatus;
use App\Constants\WorkRoom\Status as WorkRoomStatus;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomGroupServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 客户群管理-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkRoomGroupServiceInterface
     */
    protected $workRoomGroupService;

    /**
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    protected $workContactRoomService;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);
        ## 查询数据
        return $this->getRooms($params);
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 列表查询条件
        $where = [];
        ## 企业
        $corpIds = isset($user['corpIds']) ? $user['corpIds'] : [];
        $where[] = ['corp_id', 'IN', $corpIds];
        ## 数据权限-群主
        $ownerIdArr = empty($params['workRoomOwnerId']) ? [] : explode(',', $params['workRoomOwnerId']);
        if ($user['dataPermission'] == 0) {
            empty($ownerIdArr) || $where[] = ['owner_id', 'IN', $ownerIdArr];
        } else {
            $ownerIdArr = empty($ownerIdArr) ? $user['deptEmployeeIds'] : array_intersect($ownerIdArr, $user['deptEmployeeIds']);
            $where[]    = ['owner_id', 'IN', $ownerIdArr];
        }
        ## 客户群分组
        ! is_numeric($params['roomGroupId']) || $where['room_group_id'] = $params['roomGroupId'];
        ## 客户群名称
        empty($params['workRoomName']) || $where[] = ['name', 'LIKE', '%' . $params['workRoomName'] . '%'];
        ## 客户群状态
        ! is_numeric($params['workRoomStatus']) || $where['status'] = $params['workRoomStatus'];
        ## 客户群创建时间-startTime
        empty($params['startTime']) || $where[] = ['create_time', '>=', $params['startTime'] . ' 00:00:00'];
        ## 客户群创建时间-endTime
        empty($params['endTime']) || $where[] = ['create_time', '<=', $params['endTime'] . ' 00:00:00'];

        ## 分页信息
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'create_time desc',
        ];

        return $data = [
            'where'   => $where,
            'options' => $options,
        ];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getRooms(array $params): array
    {
        ## 分页查询数据表
        $rooms = $this->workRoomService->getWorkRoomList($params['where'], ['*'], $params['options']);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        if (empty($rooms['data'])) {
            return $data;
        }
        $data['page']['total']     = $rooms['total'];
        $data['page']['totalPage'] = $rooms['last_page'];
        ## 群主信息-群主名称-所属公司名称
        $ownerIdArr = array_unique(array_column($rooms['data'], 'ownerId'));

        $ownerList = $this->workEmployeeService->getWorkEmployeesById($ownerIdArr, ['id', 'corp_id', 'name']);

        empty($ownerList) || $ownerList = array_column($ownerList, null, 'id');

        $corpIdArr = array_unique(array_column($ownerList, 'corpId'));

        $corpList = empty($corpIdArr) ? [] : $this->corpService->getCorpsById($corpIdArr, ['id', 'name']);

        empty($corpList) || $corpList = array_column($corpList, 'name', 'id');

        ## 群分组信息
        $roomGroupIdArr = array_filter(array_unique(array_column($rooms['data'], 'roomGroupId')));

        $roomGroupList = $this->workRoomGroupService->getWorkRoomGroupsById($roomGroupIdArr, ['id', 'name']);

        empty($roomGroupList) || $roomGroupList = array_column($roomGroupList, 'name', 'id');

        ## 处理列表数据
        $list = [];
        foreach ($rooms['data'] as $room) {
            ## 群主信息
            $owner     = isset($ownerList[$room['ownerId']]) ? $ownerList[$room['ownerId']] : [];
            $corp      = (! empty($owner) && isset($corpList[$owner['corpId']])) ? $corpList[$owner['corpId']] : '';
            $ownerName = (! empty($owner['name']) && ! empty($corp)) ? $corp . '-' . $owner['name'] : (! empty($owner['name']) ? $owner['name'] : '');
            ## 群成员数量统计
            $workContactRoomList = $this->workContactRoomService->getWorkContactRoomsByRoomId((int) $room['id'], ['id', 'join_time', 'status', 'out_time']);

            // 群成员数量
            $memberNum = 0;
            // 今日入群数量
            $inRoomNum = 0;
            // 今日退群数量
            $outRoomNum = 0;

            if (! empty($workContactRoomList)) {
                foreach ($workContactRoomList as $workContactRoom) {
                    $workContactRoom['status'] == WorkContactRoomStatus::NORMAL && ++$memberNum;
                    $workContactRoom['joinTime'] >= date('Y-m-d') . ' 00:00:00' && ++$inRoomNum;
                    $workContactRoom['outTime'] >= date('Y-m-d') . ' 00:00:00' && ++$outRoomNum;
                }
            }

            $list[] = [
                'workRoomId' => $room['id'],
                'memberNum'  => $memberNum,
                'roomName'   => $room['name'],
                'ownerName'  => $ownerName,
                'roomGroup'  => isset($roomGroupList[$room['roomGroupId']]) ? $roomGroupList[$room['roomGroupId']] : '',
                'status'     => $room['status'],
                'statusText' => WorkRoomStatus::getMessage($room['status']),
                'inRoomNum'  => $inRoomNum,
                'outRoomNum' => $outRoomNum,
                'notice'     => $room['notice'],
                'createTime' => $room['createTime'],
            ];
        }

        $data['list'] = $list;

        return $data;
    }
}
