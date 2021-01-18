<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 渠道码列表.
 *
 * Class ContactLogic
 */
class ContactLogic
{
    /**
     * 客户 - 员工关联.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $workContactEmployee;

    /**
     * 客户.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $workContact;

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

    public function handle(array $params)
    {
        $this->params = $params;

        //查询客户数据
        return $this->getContact();
    }

    /**
     * 获取客户数据.
     * @return array
     */
    private function getContact()
    {
        //渠道码标识
        $state          = 'channelCode-' . $this->params['channelCodeId'];
        $where['state'] = [$state];
        $options        = [
            'orderByRaw' => 'create_time desc',
            'perPage'    => isset($this->params['perPage']) ? (int) $this->params['perPage'] : 15,
            'page'       => isset($this->params['page']) ? (int) $this->params['page'] : 1,
            'pageName'   => 'page',
        ];

        $columns = [
            'contact_id',
            'employee_id',
            'create_time',
        ];

        //分页查询数据
        $info = $this->workContactEmployee->getWorkContactEmployeeList($where, $columns, $options);

        if (empty($info['data'])) {
            return [
                'page' => [
                    'perPage'   => 15,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list' => [],
            ];
        }

        $contactIds = array_unique(array_column($info['data'], 'contactId'));
        //根据客户id查询客户信息
        $contactInfo = $this->getContactInfo($contactIds);
        //员工id
        $employeeIds = array_unique(array_column($info['data'], 'employeeId'));
        //获取成员信息
        $employeeInfo = $this->getEmployee($employeeIds);

        foreach ($info['data'] as &$val) {
            $val['name'] = '';
            if (isset($contactInfo[$val['contactId']])) {
                $val['name'] = $contactInfo[$val['contactId']]['name'];
            }

            $val['employees'] = [];
            if (isset($employeeInfo[$val['employeeId']])) {
                $val['employees'] = $employeeInfo[$val['employeeId']]['corpName'] . ' ' . $employeeInfo[$val['employeeId']]['name'];
            }
        }

        unset($val);

        return [
            'page' => [
                'perPage'   => isset($info['per_page']) ? $info['per_page'] : 15,
                'total'     => isset($info['total']) ? $info['total'] : 0,
                'totalPage' => isset($info['last_page']) ? $info['last_page'] : 0,
            ],
            'list' => $info['data'],
        ];
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
    private function getContactInfo($contactIds)
    {
        $contact = $this->workContact->getWorkContactsById($contactIds, ['id', 'name']);
        if (empty($contact)) {
            return [];
        }

        return array_column($contact, null, 'id');
    }
}
