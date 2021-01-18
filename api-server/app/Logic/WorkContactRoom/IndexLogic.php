<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContactRoom;

use App\Constants\WorkContactRoom\joinScene as WorkContactRoomJoinScene;
use App\Constants\WorkContactRoom\Status as WorkContactRoomStatus;
use App\Constants\WorkContactRoom\Type as WorkContactRoomType;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 客户群成员管理-列表.
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
     * @var WorkContactServiceInterface
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    protected $workContactRoomService;

    /**
     * 企业通讯录成员列表.
     * @var array
     */
    protected $employeeList = [];

    /**
     * 企业外部联系人列表.
     * @var array
     */
    protected $contactList = [];

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $params): array
    {
        ## 查询客户群基本信息
        $room = $this->getWorkRoom((int) $params['workRoomId']);
        ## 处理请求参数
        $params = $this->handleParams($room, $params);
        ## 查询数据
        return $this->getContactRooms($room, $params);
    }

    /**
     * @param int $workRoomId 客户群ID
     * @return array 响应数组
     */
    private function getWorkRoom(int $workRoomId): array
    {
        $data = $this->workRoomService->getWorkRoomById($workRoomId, ['id', 'corp_id', 'owner_id']);

        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前客户群不存在');
        }
        return $data;
    }

    /**
     * @param array $params 请求参数
     * @param array $room 客户群基础信息
     * @return array 响应数组
     */
    private function handleParams(array $room, array $params): array
    {
        ## 组织返回数据
        $data = [
            'where'   => [],
            'options' => [
                'page'       => $params['page'],
                'perPage'    => $params['perPage'],
                'orderByRaw' => 'id desc',
            ],
            'hadName' => 0,  ## 是否存在成员名称的模糊搜索
        ];
        ## 客户群ID
        $data['where']['workRoomId'] = $params['workRoomId'];
        ## 客户群成员状态
        empty($params['status']) || $data['where']['status'] = $params['status'];
        ## 成员加入群聊时间-startTime
        empty($params['startTime']) || $data['where']['joinTimeStart'] = $params['startTime'] . ' 00:00:00';
        ## 成员加入群聊时间-endTime
        empty($params['endTime']) || $data['where']['joinTimeEnd'] = $params['endTime'] . ' 00:00:00';
        ## 处理客户群成员名称
        if (! empty($params['name'])) {
            $data['hadName'] = 1;
            ## 企业通讯录成员模糊匹配
            $this->getEmployees((int) $room['corpId'], $params['name']);
            ## 企业外部联系人模糊匹配
            $this->getContacts((int) $room['corpId'], $params['name']);
            if (empty($this->employeeList) && empty($this->contactList)) {
                unset($data['where']);
            } else {
                $data['where']['employeeIds'] = array_column($this->employeeList, 'id');
                $data['where']['contactIds']  = array_column($this->contactList, 'id');
            }
        }

        return $data;
    }

    /**
     * @param int $corpId 企业微信授信ID
     * @param string $name 通讯录成员名称
     */
    private function getEmployees(int $corpId, string $name)
    {
        $data = $this->workEmployeeService->getWorkEmployeesByCorpIdName($corpId, $name, ['id', 'name', 'avatar']);

        $this->employeeList = ! empty($data) ? array_column($data, null, 'id') : [];
    }

    /**
     * @param int $corpId 企业微信授信ID
     * @param string $name 通讯录成员名称
     */
    private function getContacts(int $corpId, string $name)
    {
        $data = $this->workContactService->getWorkContactsByCorpIdName($corpId, $name, ['id', 'name', 'avatar']);

        $this->contactList = ! empty($data) ? array_column($data, null, 'id') : [];
    }

    /**
     * @param array $params 请求参数
     * @param array $room 客户群基本信息
     * @return array 响应数组
     */
    private function getContactRooms(array $room, array $params): array
    {
        ## 组织响应数据
        $data = [
            'memberNum'  => 0,
            'outRoomNum' => 0,
            'page'       => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        ## 群成员数量统计
        $workContactRoomList = $this->workContactRoomService->getWorkContactRoomsByRoomId((int) $params['where']['workRoomId'], ['id', 'join_time', 'status']);

        if (! empty($workContactRoomList)) {
            foreach ($workContactRoomList as $workContactRoom) {
                $workContactRoom['status'] == WorkContactRoomStatus::NORMAL && ++$data['memberNum'];
                $workContactRoom['status'] == WorkContactRoomStatus::QUIT && ++$data['outRoomNum'];
            }
        }

        if (! isset($params['where'])) {
            return $data;
        }
        ## 分页查询数据表
        $contactRooms = $this->workContactRoomService->getWorkContactRoomIndex($params['where'], ['*'], (int) $params['options']['perPage'], (int) $params['options']['page']);

        if (empty($contactRooms['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total']     = $contactRooms['total'];
        $data['page']['totalPage'] = $contactRooms['last_page'];

        ## 获取客户群成员的基本信息
        if ($params['hadName'] == 0) {
            ## 企业通讯录成员信息
            $employeeList                               = $this->workEmployeeService->getWorkEmployeesById(array_filter(array_column($contactRooms['data'], 'employeeId')), ['id', 'name', 'avatar']);
            empty($employeeList) || $this->employeeList = array_column($employeeList, null, 'id');
            ## 外部联系人
            $contactList                              = $this->workContactService->getWorkContactsById(array_filter(array_column($contactRooms['data'], 'contactId')), ['id', 'name', 'avatar']);
            empty($contactList) || $this->contactList = array_column($contactList, null, 'id');
        }

        ## 处理列表数据
        $list = [];
        foreach ($contactRooms['data'] as $contactRoom) {
            ## 成员基本信息
            $baseInfo = [];
            ## 该成员在当前公司加入其它群聊信息
            $otherRooms = [];
            if ($contactRoom['type'] == WorkContactRoomType::EMPLOYEE) {
                ! isset($this->employeeList[$contactRoom['employeeId']]) || $baseInfo = $this->employeeList[$contactRoom['employeeId']];
            } elseif ($contactRoom['type'] == WorkContactRoomType::CONTACT) {
                ! isset($this->contactList[$contactRoom['contactId']]) || $baseInfo = $this->contactList[$contactRoom['contactId']];
            }
            $workContactRooms            = $this->workContactRoomService->getWorkContactRoomsByWxUserId($contactRoom['wxUserId'], ['room_id']);
            $otherRoomIdArr              = array_diff(array_column($workContactRooms, 'roomId'), [$contactRoom['roomId']]);
            $rooms                       = $this->workRoomService->getWorkRoomsById(array_values($otherRoomIdArr), ['name']);
            empty($rooms) || $otherRooms = array_column($rooms, 'name');

            $list[] = [
                'workContactRoomId' => $contactRoom['id'],
                'name'              => isset($baseInfo['name']) ? $baseInfo['name'] : '',
                'avatar'            => isset($baseInfo['avatar']) ? file_full_url($baseInfo['avatar']) : '',
                'isOwner'           => $contactRoom['employeeId'] == $room['ownerId'] ? 1 : 0,
                'joinTime'          => $contactRoom['joinTime'],
                'outRoomTime'       => $contactRoom['outTime'],
                'otherRooms'        => $otherRooms,
                'joinScene'         => $contactRoom['joinScene'],
                'joinSceneText'     => WorkContactRoomJoinScene::getMessage($contactRoom['joinScene']),
            ];
        }

        $data['list'] = $list;

        return $data;
    }
}
