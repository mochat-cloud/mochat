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

use App\Constants\WorkContactEmployee\Status;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 流失客户列表.
 *
 * Class LossContactLogic
 */
class LossContactLogic
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

        //返回流失客户列表
        return $this->getInfo();
    }

    /**
     * 获取流失客户列表.
     * @return array
     */
    private function getInfo()
    {
        $perPage = isset($this->params['perPage']) ? (int) $this->params['perPage'] : 20;
        $page    = isset($this->params['page']) ? (int) $this->params['page'] : 1;

        //处理查询条件
        $where = $this->handleWhere();

        if (isset($where['emptyData'])) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list' => [],
            ];
        }

        $columns = [
            'id',
            'employee_id',
            'contact_id',
            'deleted_at',
        ];

        //查询流失客户 （mc_work_contact_employee.status = 2,3）
        $info = $this->contactEmployee->getWorkContactEmployeeIndex($where, $columns, $perPage, $page);

        if (empty($info['data'])) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list' => [],
            ];
        }

        $contactIds  = array_column($info['data'], 'contactId');
        $employeeIds = array_column($info['data'], 'employeeId');

        //根据客户id查询客户信息
        $contactInfo = $this->getContact($contactIds);
        //查询删除客户的员工信息
        $employee = $this->getEmployee($employeeIds);

        foreach ($info['data'] as &$raw) {
            $raw['avatar'] = '';
            $raw['name']   = '';
            if (isset($contactInfo[$raw['contactId']])) {
                $raw['avatar'] = empty($contactInfo[$raw['contactId']]['avatar']) ? '' : file_full_url($contactInfo[$raw['contactId']]['avatar']);
                $raw['name']   = $contactInfo[$raw['contactId']]['name'];
            }

            //查询客户标签
            $raw['tag'] = $this->getContactTag($raw['contactId'], $raw['employeeId']);

            $raw['employeeName'] = [];
            $raw['remark']       = '';
            if (isset($employee[$raw['employeeId']])) {
                $raw['employeeName'] = $employee[$raw['employeeId']]['corpName'] . ' ' . $employee[$raw['employeeId']]['name'];
                $raw['remark']       = $employee[$raw['employeeId']]['name'];
            }

            $raw['user'] = user();
        }

        unset($raw);

        return [
            'page' => [
                'perPage'   => isset($info['per_page']) ? $info['per_page'] : 20,
                'total'     => isset($info['total']) ? $info['total'] : 0,
                'totalPage' => isset($info['last_page']) ? $info['last_page'] : 0,
            ],
            'list' => $info['data'],
        ];
    }

    /**
     * 获取客户标签.
     * @param $contactId
     * @param $employeeId
     * @return array
     */
    private function getContactTag($contactId, $employeeId)
    {
        $contactTag = $this->contactTagPivot->getWorkContactTagPivotsSoftByOtherId($contactId, $employeeId);
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
     * 处理查询条件.
     * @return array
     */
    private function handleWhere()
    {
        $where = [];
        //企业id
        $where['corpId'] = user()['corpIds'];
        //状态
        $where['status'] = [Status::REMOVE, Status::PASSIVE_REMOVE];

        //数据权限 0-全企业，1-本部门数据，2-当前登录人
        if (user()['dataPermission'] != 0) {
            //员工id
            $where['employeeId'] = user()['deptEmployeeIds'];
        }

        $where['isFlag'] = 1; //标明是流失客户

        if (! empty($this->params['employeeId'])) {
            $employeeId = explode(',', $this->params['employeeId']);
            if (isset($where['employeeId'])) {
                $where['employeeId'] = array_intersect($where['employeeId'], $employeeId);
                //如果交集为空 则说明查询不出数据
                if (empty($where['employeeId'])) {
                    $where['emptyData'] = 1;
                }
            } else {
                $where['employeeId'] = $employeeId;
            }
        }

        return $where;
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
     * 获取客户信息.
     * @param $contactIds
     * @return array
     */
    private function getContact($contactIds)
    {
        $contact = $this->contact->getWorkContactsById($contactIds, ['id', 'name', 'avatar']);
        if (empty($contact)) {
            return [];
        }

        return array_column($contact, null, 'id');
    }
}
