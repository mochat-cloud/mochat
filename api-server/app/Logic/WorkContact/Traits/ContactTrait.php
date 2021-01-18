<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContact\Traits;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;

trait ContactTrait
{
    /**
     * 获取客户归属成员信息.
     * @param $contactIds
     * @return array
     */
    private function getContactEmployee($contactIds)
    {
        $workContactEmployee = make(WorkContactEmployeeServiceInterface::class);

        $contactEmployee = $workContactEmployee->getWorkContactEmployeesByContactIds($contactIds, ['contact_id', 'employee_id']);
        if (empty($contactEmployee)) {
            return [];
        }
        $employeeIds = array_column($contactEmployee, 'employeeId');

        //获取成员信息
        $employeeInfo = $this->getEmployee($employeeIds);

        $contact = [];
        foreach ($contactEmployee as &$raw) {
            $raw['employeeName'] = [];
            if (isset($employeeInfo[$raw['employeeId']])) {
                $raw['employeeName'] = $employeeInfo[$raw['employeeId']]['corpName'] . ' ' . $employeeInfo[$raw['employeeId']]['name'];
            }

            if (isset($contact[$raw['contactId']])) {
                $contact[$raw['contactId']]['employeeName'][] = $raw['employeeName'];
            } else {
                $tmp                   = [];
                $tmp['employeeName'][] = $raw['employeeName'];

                $contact[$raw['contactId']] = $tmp;
            }
        }
        unset($raw);

        return $contact;
    }

    /**
     * 查询员工信息.
     * @param $employeeIds
     * @return array
     */
    private function getEmployee($employeeIds)
    {
        $employee = make(WorkEmployeeServiceInterface::class);
        $corp     = make(CorpServiceInterface::class);

        //根据员工id查询员工信息、企业id
        $employeeInfo = $employee->getWorkEmployeesById($employeeIds, ['id', 'name', 'corp_id']);

        if (empty($employeeInfo)) {
            return [];
        }

        $corpIds = array_unique(array_column($employeeInfo, 'corpId'));

        //查询企业名称
        $corpInfo = $corp->getCorpsById($corpIds, ['id', 'name']);
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
     * 获取客户标签信息.
     * @param $contactIds
     * @return array
     */
    private function getTag($contactIds)
    {
        $contactTag      = make(WorkContactTagServiceInterface::class);
        $contactTagPivot = make(WorkContactTagPivotServiceInterface::class);

        $contactTagInfo = $contactTagPivot->getWorkContactTagPivotsSoftByContactIds($contactIds, ['contact_id', 'contact_tag_id']);
        if (empty($contactTagInfo)) {
            return [];
        }

        $tagIds = array_column($contactTagInfo, 'contactTagId');
        //根据标签id查询标签名称（含软删数据）
        $tagInfo = $contactTag->getWorkContactTagsSoftById($tagIds, ['id', 'name']);

        if (empty($tagInfo)) {
            return [];
        }

        $tagInfo = array_column($tagInfo, null, 'id');

        foreach ($contactTagInfo as &$raw) {
            $raw['tagName'] = '';
            if (isset($tagInfo[$raw['contactTagId']])) {
                $raw['tagName'] = $tagInfo[$raw['contactTagId']]['name'];
            }
        }
        unset($raw);

        $tagData = [];
        foreach ($contactTagInfo as &$val) {
            if (isset($val['contactId'])) {
                $tagData[$val['contactId']]['tagName'][] = $val['tagName'];
            } else {
                $tmp              = [];
                $tmp['tagName'][] = $val['tagName'];

                $tagData[$val['contactId']] = $tmp;
            }
        }
        unset($val);

        return $tagData;
    }
}
