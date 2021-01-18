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

use App\Constants\WorkContact\Gender;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 查看客户详情基本信息.
 *
 * Class ShowLogic
 */
class ShowLogic
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
     * 客户 - 标签关联.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivot;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTag;

    /**
     * 客户群.
     * @Inject
     * @var WorkRoomServiceInterface
     */
    private $room;

    /**
     * 客户 - 客户群关联.
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    private $contactRoom;

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
     * 客户id.
     * @var int
     */
    private $contactId;

    public function handle(array $params): array
    {
        $this->contactId = $params['contactId'];

        $columns = [
            'id',
            'name',
            'avatar',
            'gender',
            'business_no',
        ];
        //查询客户基础信息
        $contactInfo = $this->contact->getWorkContactById((int) $this->contactId, $columns);

        //查询员工对客户的备注、描述
        $contactEmployee = $this->contactEmployee
            ->findWorkContactEmployeeByOtherIds($params['employeeId'], $this->contactId, ['remark', 'description']);

        //查询客户标签
        $contactTag = $this->getContactTag();

        //查询客户所在群
        $roomName = $this->getContactRoom();

        //查询客户归属企业成员
        $employee = $this->getEmployee();

        return [
            'name'         => isset($contactInfo['name']) ? $contactInfo['name'] : '',
            'avatar'       => isset($contactInfo['avatar']) ? file_full_url($contactInfo['avatar']) : '',
            'gender'       => isset($contactInfo['gender']) ? $contactInfo['gender'] : 0,
            'genderText'   => isset($contactInfo['gender']) ? Gender::getMessage($contactInfo['gender']) : '未知',
            'businessNo'   => isset($contactInfo['businessNo']) ? $contactInfo['businessNo'] : '',
            'remark'       => isset($contactEmployee['remark']) ? $contactEmployee['remark'] : '',
            'description'  => isset($contactEmployee['description']) ? $contactEmployee['description'] : '',
            'tag'          => $contactTag,
            'roomName'     => $roomName,
            'employeeName' => $employee,
        ];
    }

    /**
     * 获取客户所在群.
     * @return array
     */
    private function getContactRoom()
    {
        $contactRoom = $this->contactRoom->getWorkContactRoomsByContactId($this->contactId, ['room_id']);
        if (empty($contactRoom)) {
            return [];
        }

        $roomIds = array_column($contactRoom, 'roomId');
        //根据群id查询群名称
        $roomInfo = $this->room->getWorkRoomsById($roomIds, ['id', 'name']);

        if (empty($roomInfo)) {
            return [];
        }

        return array_column($roomInfo, 'name');
    }

    /**
     * 获取客户标签.
     * @return array
     */
    private function getContactTag()
    {
        $contactTag = $this->contactTagPivot->getWorkContactTagPivotsByContactId($this->contactId, ['contact_tag_id']);
        if (empty($contactTag)) {
            return [];
        }

        $tagIds = array_column($contactTag, 'contactTagId');
        //根据标签id查询标签名称
        $tagInfo = $this->contactTag->getWorkContactTagsById($tagIds, ['id', 'name']);

        if (empty($tagInfo)) {
            return [];
        }
        array_walk($tagInfo, function (&$item) {
            $item['tagId'] = $item['id'];
            $item['tagName'] = $item['name'];

            unset($item['id'], $item['name']);
        });

        return $tagInfo;
    }

    /**
     * 获取客户归属企业成员.
     * @return array
     */
    private function getEmployee()
    {
        //查询客户归属企业成员
        $contactEmployee = $this->contactEmployee->getWorkContactEmployeesByContactId((int) $this->contactId, ['employee_id']);

        if (empty($contactEmployee)) {
            return [];
        }

        $employeeIds = array_column($contactEmployee, 'employeeId');
        //根据员工id查询员工姓名
        $employeeInfo = $this->employee->getWorkEmployeesById($employeeIds, ['id', 'name', 'corp_id']);

        if (empty($employeeInfo)) {
            return [];
        }

        $employeeInfo = array_column($employeeInfo, null, 'id');
        foreach ($contactEmployee as &$raw) {
            if (isset($employeeInfo[$raw['employeeId']])) {
                $raw['employeeName'] = $employeeInfo[$raw['employeeId']]['name'];
                $raw['corpId']       = $employeeInfo[$raw['employeeId']]['corpId'];
            }
        }
        unset($raw);

        $corpIds = array_unique(array_column($employeeInfo, 'corpId'));

        //查询企业名称
        $corpInfo = $this->corp->getCorpsById($corpIds, ['id', 'name']);

        if (! empty($corpInfo)) {
            $corpInfo = array_column($corpInfo, null, 'id');

            foreach ($contactEmployee as &$val) {
                if (isset($corpInfo[$val['corpId']])) {
                    $val['corpName'] = $corpInfo[$val['corpId']]['name'];
                }
            }
            unset($val);
        }

        $data = [];
        foreach ($contactEmployee as $value) {
            $data[] = $value['corpName'] . ' ' . $value['employeeName'];
        }

        return $data;
    }
}
