<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContact;

use App\Constants\WorkContact\AddWay;
use App\Constants\WorkContact\Gender;
use App\Constants\WorkContactEmployee\Status;
use App\Constants\WorkContactRoom\Type as ContactType;
use App\Constants\WorkUpdateTime\Type;
use App\Contract\ContactFieldPivotServiceInterface;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 客户列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * 客户表.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $contact;

    /**
     * 员工 - 客户关联.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $contactEmployee;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTag;

    /**
     * 客户 - 标签关联.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivot;

    /**
     * 用户画像.
     * @Inject
     * @var ContactFieldPivotServiceInterface
     */
    private $contactFieldPivot;

    /**
     * 客户群.
     * @Inject
     * @var WorkRoomServiceInterface
     */
    private $room;

    /**
     * 客户 - 群关联.
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    private $contactRoom;

    /**
     * 同步时间.
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

    /**
     * 员工.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $employee;

    /**
     * 企业表.
     * @Inject
     * @var CorpServiceInterface
     */
    private $corp;

    /**
     * 参数.
     * @var array
     */
    private $params;

    /**
     * @return array
     */
    public function handle(array $params)
    {
        $this->params = $params;

        //处理查询条件
        $where = $this->handleWhere($this->params);
        //标明没有数据
        if (isset($where['flag'])) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list'            => [],
                'syncContactTime' => '',
            ];
        }
        unset($where['flag']);

        //获取客户列表
        return $this->getContactEmployeeInfo($where);
    }

    /**
     * 处理客户列表查询条件.
     * @param $params
     */
    protected function handleWhere($params): array
    {
        $where = [];
        //企业id
        $where['corpId'] = user()['corpIds'];

        //数据权限 0-全企业，1-本部门数据，2-当前登录人
        if (user()['dataPermission'] != 0) {
            //员工id
            $where['employeeId'] = array_unique(user()['deptEmployeeIds']);
        }

        //状态为正常
        $where['status'] = Status::NORMAL;

        //备注搜索
        if (! empty($params['remark'])) {
            $where['remark'] = $params['remark'];
        }
        //客户来源搜索
        if (is_numeric($params['addWay'])) {
            $where['addWay'] = $params['addWay'];
        }
        //添加时间搜索
        if (! empty($params['startTime'])) {
            $where['startTime'] = $params['startTime'];
        }
        if (! empty($params['endTime'])) {
            $where['endTime'] = $params['endTime'];
        }

        //部门成员搜索
        if (! empty($params['employeeId'])) {
            if ($params['employeeId'] == '空') {
                //查询不出数据 的标识
                $where['flag'] = 1;
            }
            $employeeIds = explode(',', $params['employeeId']);

            if (isset($where['employeeId'])) {
                $where['employeeId'] = array_intersect($where['employeeId'], $employeeIds);
            } else {
                $where['employeeId'] = $employeeIds;
            }
        }

        //关键词搜索
        if (! empty($params['keyWords'])) {
            $name    = $params['keyWords'];
            $contact = $this->contact->getWorkContactsByCorpIdName(user()['corpIds'][0], $name, ['id']);
            if (! empty($contact)) {
                $contactIds = array_column($contact, 'id');
                if (isset($where['contactId'])) {
                    //取交集
                    $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                } else {
                    $where['contactId'] = $contactIds;
                }
            } else {
                //查询不出数据 的标识
                $where['flag'] = 1;
            }
        }
        //客户编号搜索
        if (! empty($params['businessNo'])) {
            $contact = $this->contact->getWorkContactsByCorpIdBusinessNo(user()['corpIds'][0], $params['businessNo'], ['id']);
            if (! empty($contact)) {
                $contactIds = array_column($contact, 'id');
                if (isset($where['contactId'])) {
                    //取交集
                    $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                } else {
                    $where['contactId'] = $contactIds;
                }
            } else {
                //查询不出数据的标识
                $where['flag'] = 1;
            }
        }
        //客户性别搜索
        if (isset($this->params['gender']) && $params['gender'] != 3) {
            $contact = $this->contact->getWorkContactsByCorpIdGender(user()['corpIds'][0], $params['gender'], ['id']);
            if (! empty($contact)) {
                $contactIds = array_column($contact, 'id');
                if (isset($where['contactId'])) {
                    //取交集
                    $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                } else {
                    $where['contactId'] = $contactIds;
                }
            } else {
                //查询不出数据的标识
                $where['flag'] = 1;
            }
        }
        //用户画像搜索
        if (is_numeric($params['fieldId']) && $params['fieldId'] != 0) {
            //查询满足用户画像值的用户id
            $pivot = $this->contactFieldPivot
                ->getContactFieldPivotsByFieldIdValue($params['fieldId'], $params['fieldValue'], ['contact_id']);
            if (! empty($pivot)) {
                $contactIds = array_column($pivot, 'contactId');

                if (isset($where['contactId'])) {
                    //取交集
                    $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                } else {
                    $where['contactId'] = $contactIds;
                }
            } else {
                //查询不出数据的标识
                $where['flag'] = 1;
            }
        }
        //群聊搜索
        if (! empty($params['roomId'])) {
            $roomIds = explode(',', $params['roomId']);
            //查询在群聊里的客户id
            $roomInfo = $this->contactRoom->getWorkContactRoomsByRoomIds($roomIds, ['contact_id']);
            if (! empty($roomInfo)) {
                $contactIds = array_unique(array_column($roomInfo, 'contactId'));
                if (isset($where['contactId'])) {
                    //取交集
                    $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                } else {
                    $where['contactId'] = $contactIds;
                }
            } else {
                //查询不出数据的标识
                $where['flag'] = 1;
            }
        }
        //客户持群数搜索
        if (is_numeric($params['groupNum']) && $params['groupNum'] != 3) {
            $roomInfo = $this->contactRoom->getWorkContactRoomNum();
            if (! empty($roomInfo)) {
                //查出来有群的客户id
                $noContactIds = array_column($roomInfo, 'contactId');
                //搜索条件为无群
                if ($params['groupNum'] == 0) {
                    $where['noContactId'] = $noContactIds;
                } else {
                    $contactIds = [];
                    foreach ($roomInfo as &$raw) {
                        //搜索条件为一个群
                        if ($params['groupNum'] == 1 && $raw['roomTotal'] == $params['groupNum']) {
                            $contactIds[] = $raw['contactId'];
                        }
                        //搜索条件为多群
                        if ($params['groupNum'] == 2 && $raw['roomTotal'] >= 2) {
                            $contactIds[] = $raw['contactId'];
                        }
                    }
                    unset($raw);

                    if (isset($where['contactId'])) {
                        $where['contactId'] = array_intersect($where['contactId'], $contactIds);
                    } else {
                        $where['contactId'] = $contactIds;
                    }
                }
            } else {
                //客户均没有群但是搜索条件不是无群时 查不出来数据
                if ($params['groupNum'] != 0) {
                    //查询不出数据的标识
                    $where['flag'] = 1;
                }
            }
        }

        //交集为空时 即查不出来数据
        if (isset($where['contactId']) && empty($where['contactId'])) {
            $where['flag'] = 1;
        }

        return $where;
    }

    /**
     * 获取客户列表.
     * @param $where
     * @return array
     */
    private function getContactEmployeeInfo($where)
    {
        $columns = [
            'id',
            'employee_id',
            'contact_id',
            'remark',
            'create_time',
            'add_way',
        ];

        //查询数据
        $info = $this->contactEmployee->getWorkContactEmployeeIndex(
            $where,
            $columns,
            (int) $this->params['perPage'],
            (int) $this->params['page']
        );

        if (empty($info['data'])) {
            return [];
        }

        //客户id
        $contactIds = array_unique(array_column($info['data'], 'contactId'));
        //员工id
        $employeeIds = array_unique(array_column($info['data'], 'employeeId'));
        //查询客户基础信息
        $contact = $this->getContactInfo($contactIds);
        //查询客户所在群
        $roomName = $this->getContactRoom($contactIds);
        //查询客户归属成员信息
        $employee = $this->getEmployee($employeeIds);

        foreach ($info['data'] as &$raw) {
            $raw['addWayText'] = AddWay::$Enum[$raw['addWay']];
            $raw['genderText'] = '';
            $raw['businessNo'] = '';
            $raw['name']       = '';
            $raw['avatar']     = '';
            if (isset($contact[$raw['contactId']])) {
                $raw['businessNo'] = $contact[$raw['contactId']]['businessNo'];
                $raw['name']       = $contact[$raw['contactId']]['name'];
                $raw['avatar']     = $contact[$raw['contactId']]['avatar'] ? file_full_url($contact[$raw['contactId']]['avatar']) : $contact[$raw['contactId']]['avatar'];
                $raw['gender']     = $contact[$raw['contactId']]['gender'];
                $raw['genderText'] = Gender::getMessage($contact[$raw['contactId']]['gender']);
            }

            $raw['roomName'] = [];
            if (isset($roomName[$raw['contactId']])) {
                $raw['roomName'] = $roomName[$raw['contactId']]['roomName'];
            }

            $raw['employeeName'] = [];
            if (isset($employee[$raw['employeeId']])) {
                $raw['employeeName'] = $employee[$raw['employeeId']]['corpName'] . ' ' . $employee[$raw['employeeId']]['name'];
            }

            //查询客户标签
            $raw['tag'] = $this->getContactTag($raw['contactId'], $raw['employeeId']);
            //判断该客户是否是当前登录人的客户
            $raw['isContact'] = 1; //是
            if (user()['workEmployeeId'] != $raw['employeeId']) {
                $raw['isContact'] = 2;
            }

            $raw['user'] = user();
        }

        //返回数据
        $data = [
            'page' => [
                'perPage'   => isset($info['per_page']) ? $info['per_page'] : 20,
                'total'     => isset($info['total']) ? $info['total'] : 0,
                'totalPage' => isset($info['last_page']) ? $info['last_page'] : 0,
            ],
            'list'            => empty($info['data']) ? [] : $info['data'],
            'syncContactTime' => $this->getTime(),
        ];

        return $data;
    }

    /**
     * 获取客户信息.
     * @param $contactIds
     * @return array
     */
    private function getContactInfo($contactIds)
    {
        $contact = $this->contact->getWorkContactsById($contactIds);
        if (! empty($contact)) {
            $contact = array_column($contact, null, 'id');
        }

        return $contact;
    }

    /**
     * 查询员工信息.
     * @param $employeeIds
     * @return array
     */
    private function getEmployee($employeeIds)
    {
        //根据员工id查询员工信息、企业id
        $employeeInfo = $this->employee->getWorkEmployeesById($employeeIds, ['id', 'name', 'corp_id']);

        if (empty($employeeInfo)) {
            return [];
        }

        $corpIds = array_unique(array_column($employeeInfo, 'corpId'));

        //查询企业名称
        $corpInfo = $this->corp->getCorpsById($corpIds, ['id', 'name']);
        if (empty($corpInfo)) {
            return [];
        }
        $corpInfo = array_column($corpInfo, null, 'id');

        foreach ($employeeInfo as &$raw) {
            $raw['corpName'] = '';

            if (isset($corpInfo[$raw['corpId']])) {
                $raw['corpName'] = $corpInfo[$raw['corpId']]['name'];
            }
        }

        unset($raw);

        $employeeInfo = array_column($employeeInfo, null, 'id');

        return $employeeInfo;
    }

    /**
     * 获取客户标签.
     * @param $contactId
     * @param $employeeId
     * @return array
     */
    private function getContactTag($contactId, $employeeId)
    {
        $contactTag = $this->contactTagPivot->getWorkContactTagPivotsByOtherId($contactId, $employeeId);
        if (empty($contactTag)) {
            return [];
        }

        $tagIds = array_unique(array_column($contactTag, 'contactTagId'));
        //根据标签id查询标签名称
        $tagInfo = $this->contactTag->getWorkContactTagsById($tagIds, ['id', 'name']);

        if (empty($tagInfo)) {
            return [];
        }

        $tagInfo = array_column($tagInfo, null, 'id');

        $tagName = [];
        foreach ($contactTag as &$raw) {
            if (isset($tagInfo[$raw['contactTagId']])) {
                $tagName[] = $tagInfo[$raw['contactTagId']]['name'];
            }
        }
        unset($raw);

        $tagName = array_unique($tagName);

        return $tagName;
    }

    /**
     * 获取客户所在群.
     * @param $contactIds
     * @return array
     */
    private function getContactRoom($contactIds)
    {
        $contactRoom = $this->contactRoom->getWorkContactRoomsByContactIdsType($contactIds, (int) ContactType::CONTACT, ['contact_id', 'room_id']);
        if (empty($contactRoom)) {
            return [];
        }

        $roomIds = array_column($contactRoom, 'roomId');
        //根据群id查询群名称
        $roomInfo = $this->room->getWorkRoomsById($roomIds, ['id', 'name']);

        if (! empty($roomInfo)) {
            $roomInfo = array_column($roomInfo, null, 'id');
        }

        $contactData = [];
        foreach ($contactRoom as &$raw) {
            $raw['roomName'] = '';
            if (isset($roomInfo[$raw['roomId']])) {
                $raw['roomName'] = $roomInfo[$raw['roomId']]['name'];
            }

            if (isset($contactData[$raw['contactId']])) {
                $contactData[$raw['contactId']]['roomName'][] = $raw['roomName'];
            } else {
                $tmp               = [];
                $tmp['roomName'][] = $raw['roomName'];

                $contactData[$raw['contactId']] = $tmp;
            }
        }

        unset($raw);

        return $contactData;
    }

    /**
     * 获取最后一次同步客户的时间.
     * @return mixed
     */
    private function getTime()
    {
        $type = Type::CONTACT;

        $res = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType(user()['corpIds'], (int) $type, ['last_update_time']);

        return empty($res) ? '' : end($res)['lastUpdateTime'];
    }
}
