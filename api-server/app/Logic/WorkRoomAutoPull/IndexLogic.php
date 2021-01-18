<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkRoomAutoPull;

use App\Constants\BusinessLog\Event;
use App\Constants\WorkContactRoom\Status as WorkContactRoomStatus;
use App\Constants\WorkContactRoom\Type as WorkContactRoomType;
use App\Constants\WorkRoomAutoPull\DrawState;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomAutoPullServiceInterface;
use App\Contract\WorkRoomGroupServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\Logic\Common\Traits\BusinessLogTrait;
use Hyperf\Di\Annotation\Inject;

/**
 * 自动拉群管理-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    use BusinessLogTrait;

    /**
     * @Inject
     * @var WorkRoomAutoPullServiceInterface
     */
    protected $workRoomAutoPullService;

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
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    protected $workContactTagService;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);
        ## 检索数据
        return $this->workRoomAutoPulls($params);
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 组织响应数据
        $data = [
            'where'   => [],
            'options' => [
                'page'       => $params['page'],
                'perPage'    => $params['perPage'],
                'orderByRaw' => 'id desc',
            ],
        ];
        ## 数据权限
        $user['dataPermission'] == 0 || $data['where'][] = ['id', 'IN', $this->getBusinessIds($user['deptEmployeeIds'], [Event::ROOM_AUTO_PULL_CREATE])];
        ## 自动拉群名称
        empty($params['qrcodeName']) || $data['where'][] = ['qrcode_name', 'LIKE',  '%' . $params['qrcodeName'] . '%'];
        ## 归属企业
        $corpIds         = isset($user['corpIds']) ? $user['corpIds'] : [];
        $data['where'][] = ['corp_id', 'IN',  $corpIds];

        return $data;
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function workRoomAutoPulls(array $params): array
    {
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        ## 查表
        $res = $this->workRoomAutoPullService->getWorkRoomAutoPullList($params['where'], ['*'], $params['options']);

        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];

        ## 处理列表数据
        $employeeIds = [];
        $tagIds      = [];
        $roomIds     = [];
        foreach ($res['data'] as $v) {
            empty($v['employees']) || $employeeIds = array_merge($employeeIds, json_decode($v['employees'], true));
            empty($v['tags']) || $tagIds           = array_merge($tagIds, json_decode($v['tags'], true));
            empty($v['rooms']) || $roomIds         = array_merge($roomIds, array_column(json_decode($v['rooms'], true), 'roomId'));
        }
        ## 使用成员
        $workEmployeeList = $this->getWorkEmployeeList($employeeIds);
        ## 客户标签
        $workContactTagList = $this->getWorkContactTagList($tagIds);
        ## 客户群聊
        $workRoomList = $this->getWorkRoomList($roomIds);
        ## 群数据统计
        $roomStatistics = $this->handleRoomStatistics($roomIds);

        foreach ($res['data'] as &$v) {
            $v['workRoomAutoPullId'] = $v['id'];
            $v['qrcodeUrl']          = empty($v['qrcodeUrl']) ? '' : file_full_url($v['qrcodeUrl']);
            ## 使用成员
            $employees      = json_decode($v['employees'], true);
            $v['employees'] = [];
            foreach ($employees as $employee) {
                ! isset($workEmployeeList[$employee]) || $v['employees'][] = $workEmployeeList[$employee];
            }
            ## 客户标签
            $tags      = json_decode($v['tags'], true);
            $v['tags'] = [];
            foreach ($tags as $tag) {
                ! isset($workContactTagList[$tag]) || $v['tags'][] = $workContactTagList[$tag];
            }
            ## 客户群聊
            $rooms      = json_decode($v['rooms'], true);
            $v['rooms'] = [];
            ## 客户数量
            $v['contactNum'] = 0;
            ## 客户群拉人状态
            $isDrawing = 0;
            foreach ($rooms as $room) {
                if (! isset($workRoomList[$room['roomId']])) {
                    continue;
                }
                $v['contactNum'] += $roomStatistics[$room['roomId']]['contactNum'];
                $num   = $roomStatistics[$room['roomId']]['total'];
                $state = DrawState::NO_STARTED;
                if ($num < $workRoomList[$room['roomId']]['roomMax'] && $num < $room['maxNum']) {
                    if ($isDrawing == 0) {
                        $state     = DrawState::DRAWING;
                        $isDrawing = 1;
                    }
                } else {
                    $state = DrawState::FULL;
                }
                $v['rooms'][] = [
                    'roomName'  => $workRoomList[$room['roomId']]['name'],
                    'stateText' => DrawState::getMessage($state),
                ];
            }
            unset($v['id'], $v['corpId'], $v['isVerified'], $v['updatedAt'], $v['deletedAt'],  $v['wxConfigId']);
        }
        $data['list'] = $res['data'];
        return $data;
    }

    /**
     * @param array $workEmployeeIds 通讯录成员ID数组
     * @return array 响应数组
     */
    private function getWorkEmployeeList(array $workEmployeeIds): array
    {
        $data = $this->workEmployeeService->getWorkEmployeesById($workEmployeeIds, ['id', 'name']);

        return empty($data) ? [] : array_column($data, 'name', 'id');
    }

    /**
     * @param array $workContactTagIds 客户标签ID数组
     * @return array 响应数组
     */
    private function getWorkContactTagList(array $workContactTagIds): array
    {
        ## 当前企业全部客户标签
        $data = $this->workContactTagService->getWorkContactTagsById($workContactTagIds, ['id', 'name']);

        return empty($data) ? [] : array_column($data, 'name', 'id');
    }

    /**
     * @param array $roomIds 客户群聊ID数组
     * @return array 响应数组
     */
    private function getWorkRoomList(array $roomIds): array
    {
        ## 群聊
        $data = $this->workRoomService->getWorkRoomsById($roomIds, ['id', 'name', 'room_max']);

        return empty($data) ? [] : array_column($data, null, 'id');
    }

    /**
     * @param array $roomIds 客户群聊ID数组
     * @return array 响应数组
     */
    private function handleRoomStatistics(array $roomIds): array
    {
        ## 群成员数量统计
        $workContactRoomList = $this->workContactRoomService->getWorkContactRoomsByRoomIds($roomIds, ['room_id', 'contact_id', 'type', 'status']);

        $data = [];
        if (! empty($workContactRoomList)) {
            foreach ($workContactRoomList as $workContactRoom) {
                isset($data[$workContactRoom['roomId']]) || $data[$workContactRoom['roomId']] = ['total' => 0, 'contactNum' => 0];
                $workContactRoom['status'] == WorkContactRoomStatus::NORMAL && ++$data[$workContactRoom['roomId']]['total'];
                if ($workContactRoom['status'] == WorkContactRoomStatus::NORMAL
                    && $workContactRoom['type'] == WorkContactRoomType::CONTACT
                    && $workContactRoom['contactId'] == 0) {
                    ++$data[$workContactRoom['roomId']]['contactNum'];
                }
            }
        }
        return $data;
    }
}
