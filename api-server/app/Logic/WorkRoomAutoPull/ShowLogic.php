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

use App\Constants\WorkContactRoom\Status as WorkContactRoomStatus;
use App\Constants\WorkRoomAutoPull\DrawState;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomAutoPullServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 自动拉群管理- 创建提交.
 *
 * Class ShowLogic
 */
class ShowLogic
{
    /**
     * @Inject
     * @var WorkRoomAutoPullServiceInterface
     */
    protected $workRoomAutoPullService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    protected $workContactTagGroupService;

    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    protected $workContactRoomService;

    /**
     * @param int workRoomAutoPullId 请求参数
     * @return array 响应数组
     */
    public function handle(int $workRoomAutoPullId): array
    {
        ## 自动拉群基础信息
        $roomAutoPull = $this->getRoomAutoPull($workRoomAutoPullId);
        ## 使用成员
        $workEmployeeList = $this->getWorkEmployeeList(json_decode($roomAutoPull['employees'], true));
        ## 客户标签
        $workContactTagList = $this->getWorkContactTagList(json_decode($roomAutoPull['tags'], true), (int) $roomAutoPull['corpId']);
        ## 客户群聊
        $workRoomList = $this->getWorkRoomList(json_decode($roomAutoPull['rooms'], true));

        ## 组织响应数据
        return [
            'workRoomAutoPullId' => $roomAutoPull['id'],
            'qrcodeName'         => $roomAutoPull['qrcodeName'],
            'qrcodeUrl'          => empty($roomAutoPull['qrcodeUrl']) ? '' : file_full_url($roomAutoPull['qrcodeUrl']),
            'isVerified'         => $roomAutoPull['isVerified'],
            'roomNum'            => count($workRoomList),
            'leadingWords'       => $roomAutoPull['leadingWords'],
            'createdAt'          => $roomAutoPull['createdAt'],
            'employees'          => $workEmployeeList,
            'tags'               => isset($workContactTagList['tagGroupAllList']) ? $workContactTagList['tagGroupAllList'] : [],
            'selectedTags'       => isset($workContactTagList['selectedTags']) ? $workContactTagList['selectedTags'] : [],
            'rooms'              => $workRoomList,
        ];
    }

    /**
     * @param int $workRoomAutoPullId 自动拉群ID
     * @return array 响应数组
     */
    private function getRoomAutoPull(int $workRoomAutoPullId): array
    {
        $data = $this->workRoomAutoPullService->getWorkRoomAutoPullById($workRoomAutoPullId);

        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该自动拉群不存在');
        }
        return $data;
    }

    /**
     * @param array $workEmployeeIds 通讯录成员ID数组
     * @return array 响应数组
     */
    private function getWorkEmployeeList(array $workEmployeeIds): array
    {
        $employeeList = $this->workEmployeeService->getWorkEmployeesById($workEmployeeIds, ['id', 'name']);

        $data = [];
        if (! empty($employeeList)) {
            foreach ($employeeList as $v) {
                $data[] = [
                    'employeeId'   => $v['id'],
                    'employeeName' => $v['name'],
                ];
            }
        }
        return $data;
    }

    /**
     * @param array $workContactTagIds 客户标签ID数组
     * @param int $corpId 企业授信ID
     * @return array 响应数组
     */
    private function getWorkContactTagList(array $workContactTagIds, int $corpId): array
    {
        ## 当前企业全部客户标签
        $tagList = $this->workContactTagService->getWorkContactTagsByCorpId([$corpId], ['id', 'name', 'contact_tag_group_id']);
        if (empty($tagList)) {
            return [];
        }
        ## 客户标签分组
        $contactTagGroupIds = array_filter(array_unique(array_column($tagList, 'contactTagGroupId')));
        $tagGroupList       = $this->workContactTagGroupService->getWorkContactTagGroupsById($contactTagGroupIds, ['id', 'group_name']);
        $tagGroupAllList    = [
            '0' => [
                'groupId'   => 0,
                'groupName' => '未分组',
                'list'      => [],
            ],
        ];
        if (! empty($tagGroupList)) {
            foreach ($tagGroupList as $tagGroup) {
                $tagGroupAllList[$tagGroup['id']] = [
                    'groupId'   => $tagGroup['id'],
                    'groupName' => $tagGroup['groupName'],
                    'list'      => [],
                ];
            }
        }
        ## 标签分类处理
        $selectedTags = [];
        foreach ($tagList as $tag) {
            if (! isset($tagGroupAllList[$tag['contactTagGroupId']])) {
                continue;
            }
            in_array($tag['id'], $workContactTagIds) && $selectedTags[] = $tag['id'];
            $tagGroupAllList[$tag['contactTagGroupId']]['list'][]       = [
                'tagId'      => $tag['id'],
                'tagName'    => $tag['name'],
                'isSelected' => in_array($tag['id'], $workContactTagIds) ? 1 : 2,
            ];
        }
        return [
            'tagGroupAllList' => array_values($tagGroupAllList),
            'selectedTags'    => $selectedTags,
        ];
    }

    /**
     * @param array $rooms 客户群聊数组
     * @return array 响应数组
     */
    private function getWorkRoomList(array $rooms): array
    {
        $roomIds = array_column($rooms, 'roomId');
        ## 群聊
        $roomList                     = $this->workRoomService->getWorkRoomsById($roomIds, ['id', 'name', 'room_max']);
        empty($roomList) || $roomList = array_column($roomList, null, 'id');
        ## 群数据统计
        $roomStatistics = $this->handleRoomStatistics($roomIds);
        ## 响应数据
        $data      = [];
        $isDrawing = 0;
        foreach ($rooms as $room) {
            if (! isset($roomList[$room['roomId']])) {
                continue;
            }
            if (! isset($roomStatistics[$room['roomId']])) {
                continue;
            }
            ## 客户群拉人状态
            $num   = $roomStatistics[$room['roomId']];
            $state = DrawState::NO_STARTED;
            if ($num < $roomList[$room['roomId']]['roomMax'] && $num < $room['maxNum']) {
                if ($isDrawing == 0) {
                    $state     = DrawState::DRAWING;
                    $isDrawing = 1;
                }
            } else {
                $state = DrawState::FULL;
            }

            $data[] = [
                'roomId'            => $room['roomId'],
                'roomName'          => isset($roomList[$room['roomId']]) ? $roomList[$room['roomId']]['name'] : '',
                'roomMax'           => isset($roomList[$room['roomId']]) ? $roomList[$room['roomId']]['roomMax'] : 0,
                'num'               => $roomStatistics[$room['roomId']],
                'maxNum'            => $room['maxNum'],
                'roomQrcodeUrl'     => isset($room['roomQrcodeUrl']) ? $room['roomQrcodeUrl'] : '',
                'longRoomQrcodeUrl' => isset($room['roomQrcodeUrl']) && ! empty($room['roomQrcodeUrl']) ? file_full_url($room['roomQrcodeUrl']) : '',
                'state'             => $state,
            ];
        }
        return $data;
    }

    /**
     * @param array $roomIds 客户群聊ID数组
     * @return array 响应数组
     */
    private function handleRoomStatistics(array $roomIds): array
    {
        ## 群成员数量统计
        $data = $this->workContactRoomService->countWorkContactRoomsByRoomIds($roomIds, WorkContactRoomStatus::NORMAL);
        return empty($data) ? [] : array_column($data, 'total', 'roomId');
    }
}
