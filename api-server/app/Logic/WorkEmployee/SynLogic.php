<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkEmployee;

use App\Constants\WorkEmployee\ContactAuth;
use App\Constants\WorkUpdateTime\Type;
use App\Contract\CorpServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\Framework\WeWork\WeWork;

/**
 * 成员管理-同步.
 *
 * Class SynLogic
 */
class SynLogic
{
    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @var WorkEmployeeDepartmentServiceInterface
     */
    protected $workEmployeeDepartmentService;

    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var WorkDepartmentServiceInterface
     */
    protected $workDepartmentService;

    /**
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @var WorkUpdateTimeServiceInterface
     */
    protected $workUpdateTimeService;

    public function handle(array $corpIds): bool
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($corpIds)) {
            $this->logger->error('WorkEmployeeSynLogic->handle同步创建成员corp不能为空');
            return true;
        }
        $this->client                        = make(WeWork::class);
        $this->workEmployeeService           = make(WorkEmployeeServiceInterface::class);
        $this->workDepartmentService         = make(WorkDepartmentServiceInterface::class);
        $this->workEmployeeDepartmentService = make(WorkEmployeeDepartmentServiceInterface::class);
        $this->workUpdateTimeService         = make(WorkUpdateTimeServiceInterface::class);
        //处理成员
        $this->dealEmployee($corpIds);
        //同步时间
        $this->getSysTime($corpIds);
        //上传图片
        if (! empty($ossData)) {
            oss_up_queue($ossData);
        }
        return true;
    }

    /**
     * 处理成员信息.
     * @param $corpIds
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \League\Flysystem\FileExistsException
     * @return bool
     */
    protected function dealEmployee($corpIds)
    {
        //成员基础信息
        $corpData           = $this->getCorpData($corpIds);
        $createEmployeeData = $updateEmployeeDepartment = $createEmployeeDepartmentData = $userIds = $updateEmployee = $createData = $ossData = $phones = [];
        foreach ($corpData as $corpId => $cdv) {
            $employeeData = $departments = $userList = $employeeDepartment = $employee = [];
            //公司下的所有部门
            $departments = $this->getDepartmentIds($corpId);
            if (empty($departments)) {
                continue;
            }
            //成员基础信息
            $employeeData = $this->getEmployeeData($corpId);
            //成员部门关系
            $employeeDepartment = $this->getEmployeeDepartment($employeeData['employee'], $employeeData['wxEmployee']);
            //处理部门关系数据
            $employeeDepartmentData = $this->handleEmployeeDepartment($employeeDepartment, $departments);
            //组装数据
            $employee = $this->handleDepartment($employeeDepartmentData);
            foreach ($departments as $key => $value) {
                //企业微信端用户信息
                $userList = $this->client->provider('user')->app($cdv)->user->getDetailedDepartmentUsers($value['wxDepartmentId']);
                if (! empty($userList['errcode']) || empty($userList['userlist'])) {
                    continue;
                }
                $this->handleSynData(
                    $corpId,
                    $userList,
                    $employee,
                    $departments,
                    $updateEmployeeDepartment,
                    $createEmployeeDepartmentData,
                    $createEmployeeData,
                    $updateEmployee,
                    $userIds,
                    $phones,
                    $ossData
                );
            }
            if (! empty($createEmployeeData[$corpId])) {
                //根据手机号查询子账户信息
                $createData[$corpId] = $this->getUserData($phones, $createEmployeeData[$corpId]);
            }
            if (! empty($createData[$corpId])) {
                //外部联系人权限
                $createData[$corpId] = $this->getContactAuth($cdv, $createData[$corpId]);
            }
        }
        //处理成员基础信息
        $createEmployee = $this->handleEmployee($createData);
        //创建成员基础信息
        $employeeResult = $this->createEmployee($createEmployee);
        if (empty($employeeResult)) {
            $this->logger->error('WorkEmployeeSynLogic->handle同步创建成员失败:' . json_encode($updateEmployeeDepartment));
            return true;
        }
        //创建成员部门关系
        if (! empty($createEmployeeDepartmentData)) {
            $employeeDepartmentResult = $this->createEmployeeDepartment($corpIds, $userIds, $createEmployeeDepartmentData);
            if (empty($employeeDepartmentResult)) {
                $this->logger->error('WorkEmployeeSynLogic->handle同步删除成员部门关系失败:' . json_encode($updateEmployeeDepartment));
                return true;
            }
        }
        //删除成员部门关系失败
        if (! empty($updateEmployeeDepartment)) {
            $result = $this->workEmployeeDepartmentService->deleteWorkEmployeeDepartments($updateEmployeeDepartment);
            if (empty($result)) {
                $this->logger->error('WorkEmployeeSynLogic->handle同步删除成员部门关系失败:' . json_encode($updateEmployeeDepartment));
            }
        }
        if (! empty($updateEmployee)) {
            $updateResult = $this->workEmployeeService->updateWorkEmployeesCaseIds($updateEmployee);
            if (empty($updateResult)) {
                $this->logger->error('WorkEmployeeSynLogic->handle同步头像失败:' . json_encode($updateEmployee));
            }
        }
        unset($departments, $employee, $userList);
        return true;
    }

    /**
     * 整合同步数据.
     * @param $corpId
     * @param $userList
     * @param $employee
     * @param $departments
     * @param $updateEmployeeDepartment
     * @param $createEmployeeDepartmentData
     * @param $createEmployeeData
     * @param $updateEmployee
     * @param $userIds
     * @param $phones
     * @param mixed $ossData
     * @throws \League\Flysystem\FileExistsException
     */
    protected function handleSynData(
        $corpId,
        $userList,
        $employee,
        $departments,
        &$updateEmployeeDepartment,
        &$createEmployeeDepartmentData,
        &$createEmployeeData,
        &$updateEmployee,
        &$userIds,
        &$phones,
        &$ossData
    ) {
        //获取部门员工关联信息
        foreach ($userList['userlist'] as $k => $user) {
            $userIds[$user['userid']] = $user['userid'];
            //判断是否增加过成员（是:判断是否添加过成员部门关系 否:添加成员）
            if (! empty($employee[$user['userid']])) {
                foreach ($employee[$user['userid']] as $ek => $ev) {
                    //删掉成员绑定的部门关系
                    if (! empty($user['department']) && ! in_array($ek, $user['department']) && ! empty($ek)) {
                        $updateEmployeeDepartment[$ev['employeeDepartmentId']] = $ev['employeeDepartmentId'];
                    }
                    //更新头像
                    if (empty($ev['avatar']) && ! empty($user['avatar']) && empty($updateEmployee[$ev['id']])) {
                        $updateEmployee[$ev['id']] = [
                            'id'           => $ev['id'],
                            'avatar'       => $this->ossUp($user['avatar'], 'avatar', $ossData),
                            'thumb_avatar' => $this->ossUp($user['thumb_avatar'], 'thumb_avatar', $ossData),
                        ];
                    }
                }
                foreach ($user['department'] as $dk => $dv) {
                    //判断是否存在成员部门绑定关系信息
                    if (empty($createEmployeeDepartmentData[$user['userid']][$departments[$dv]['id']]) && empty($employee[$user['userid']][$dv])) {
                        $createEmployeeDepartmentData[$user['userid']][$departments[$dv]['id']] = [
                            'employee_id'       => 0,
                            'department_id'     => ! empty($departments[$dv]['id']) ? $departments[$dv]['id'] : 0,
                            'is_leader_in_dept' => $user['is_leader_in_dept'][$dk],
                            'order'             => $user['order'][$dk],
                            'created_at'        => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            } else {
                //新增成员信息
                if (empty($createEmployeeData[$corpId][$user['userid']])) {
                    if (! empty($user['mobile'])) {
                        $phones[$user['mobile']] = $user['mobile'];
                    }
                    //头像
                    $avatar = $this->ossUp($user['avatar'], 'avatar', $ossData);
                    //头像缩图
                    $thumbAvatar = $this->ossUp($user['thumb_avatar'], 'thumb_avatar', $ossData);
                    //二维码
                    $qrCode                                       = $this->ossUp($user['qr_code'], 'qr_code', $ossData);
                    $createEmployeeData[$corpId][$user['userid']] = [
                        'wx_user_id'            => $user['userid'],
                        'corp_id'               => $corpId,
                        'name'                  => $user['name'],
                        'mobile'                => $user['mobile'],
                        'position'              => $user['position'],
                        'gender'                => $user['gender'],
                        'email'                 => $user['email'],
                        'avatar'                => $avatar,
                        'thumb_avatar'          => $thumbAvatar,
                        'telephone'             => $user['telephone'],
                        'alias'                 => $user['alias'],
                        'extattr'               => ! empty($user['extattr']) ? json_encode($user['extattr']) : json_encode([]),
                        'status'                => $user['status'],
                        'qr_code'               => $qrCode,
                        'external_profile'      => ! empty($user['external_profile']) ? json_encode($user['external_profile']) : json_encode([]),
                        'external_position'     => ! empty($user['external_position']) ? json_encode($user['external_position']) : json_encode([]),
                        'address'               => ! empty($user['address']) ? $user['address'] : '',
                        'open_user_id'          => ! empty($user['open_userid']) ? $user['open_userid'] : 0,
                        'wx_main_department_id' => $user['main_department'],
                        'main_department_id'    => ! empty($departments[$user['main_department']]['id']) ? $departments[$user['main_department']]['id'] : 0,
                        'contact_auth'          => ContactAuth::NO,
                        'log_user_id'           => 0,
                        'created_at'            => date('Y-m-d H:i:s'),
                    ];
                }
                foreach ($user['department'] as $item => $v) {
                    if (empty($createEmployeeDepartmentData[$user['userid']][$departments[$v]['id']])) {
                        $createEmployeeDepartmentData[$user['userid']][$departments[$v]['id']] = [
                            'employee_id'       => 0,
                            'department_id'     => ! empty($departments[$v]['id']) ? $departments[$v]['id'] : 0,
                            'is_leader_in_dept' => ! empty($user['is_leader_in_dept'][$item]) ? $user['is_leader_in_dept'][$item] : 0,
                            'order'             => ! empty($user['order'][$item]) ? $user['order'][$item] : 0,
                            'created_at'        => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }
    }

    /**
     * 上传文件
     * @param string $url
     * @param string $prefix
     * @param $ossData
     * @return string
     */
    protected function ossUp(string $url, string $prefix = '', &$ossData): string
    {
        if (! $url) {
            return '';
        }
        $pathUrl   = 'maochat/employee/' . $prefix . microtime(true) * 10000 . '.png';
        $ossData[] = [$url, $pathUrl];
        return $pathUrl;
    }

    /**
     * 处理成员基础信息.
     * @param $employeeData
     * @return array
     */
    protected function handleEmployee($employeeData)
    {
        if (empty($employeeData)) {
            return [];
        }
        foreach ($employeeData as $corp => $employee) {
            foreach ($employee as $ek => $ev) {
                $data[] = $ev;
            }
        }
        return $data;
    }

    /**
     * 获取成员信息.
     * @param $corpId
     * @return array
     */
    protected function getEmployeeData($corpId)
    {
        $returnData = $wxEmployeeData = [];
        //公司成员信息
        $employeeData = $this->workEmployeeService->getWorkEmployeesByCorpId($corpId, ['id', 'wx_user_id', 'avatar', 'thumb_avatar']);
        if (empty($employeeData)) {
            return ['employee' => $returnData, 'wxEmployee' => $wxEmployeeData];
        }
        foreach ($employeeData as $ek => $ev) {
            $returnData[$ev['id']] = $wxEmployeeData[$ev['wxUserId']][0] = [
                'id'                   => $ev['id'],
                'wxUserId'             => $ev['wxUserId'],
                'employeeDepartmentId' => 0,
                'workDepartmentId'     => 0,
                'wxDepartmentId'       => 0,
                'avatar'               => $ev['avatar'],
                'thumbAvatar'          => $ev['thumbAvatar'],
            ];
        }
        return ['employee' => $returnData, 'wxEmployee' => $wxEmployeeData];
    }

    /**
     * 获取成员和部门关系.
     * @param $employeeData
     * @param mixed $wxEmployeeDepartment
     * @return array
     */
    protected function getEmployeeDepartment($employeeData, $wxEmployeeDepartment)
    {
        $employeeDepartment = $wxUserIds = [];
        if (empty($employeeData)) {
            return [];
        }
        $employeeIds = array_column($employeeData, 'id');
        //获取成员部门关系信息
        $employeeDepartmentData = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeIds($employeeIds, ['employee_id', 'department_id', 'id']);
        if (empty($employeeDepartmentData)) {
            return $wxEmployeeDepartment;
        }
        foreach ($employeeDepartmentData as $edk => $edv) {
            $wxUserIds[]                                                                             = $employeeData[$edv['employeeId']]['wxUserId'];
            $employeeDepartment[$employeeData[$edv['employeeId']]['wxUserId']][$edv['departmentId']] = [
                'id'                   => $edv['employeeId'],
                'wxUserId'             => $employeeData[$edv['employeeId']]['wxUserId'],
                'employeeDepartmentId' => $edv['id'],
                'workDepartmentId'     => $edv['departmentId'],
                'wxDepartmentId'       => 0,
                'avatar'               => $employeeData[$edv['employeeId']]['avatar'],
                'thumbAvatar'          => $employeeData[$edv['employeeId']]['thumbAvatar'],
            ];
        }
        foreach ($employeeData as $wek => $wev) {
            if (! in_array($wev['wxUserId'], $wxUserIds)) {
                $employeeDepartment[$wev['wxUserId']][0] = $wev;
            }
        }
        unset($employeeIds, $employeeDepartmentData, $wxUserIds);
        return $employeeDepartment;
    }

    /**
     * 处理成员部门数据.
     * @param $employeeDepartment
     * @param $departments
     * @return array
     */
    protected function handleEmployeeDepartment($employeeDepartment, $departments)
    {
        if (empty($employeeDepartment)) {
            return [];
        }
        foreach ($departments as $dk => $dv) {
            $departmentIds[$dv['id']] = $dk;
        }
        foreach ($employeeDepartment as $edk => $employee) {
            foreach ($employee as $ek => $ev) {
                if (! empty($departmentIds[$ev['workDepartmentId']])) {
                    $employeeDepartment[$edk][$ek]['wxDepartmentId'] = $departmentIds[$ev['workDepartmentId']];
                }
            }
        }
        return $employeeDepartment;
    }

    /**
     * 处理部门数据.
     * @param $data
     * @return array
     */
    protected function handleDepartment($data)
    {
        $employee = [];
        if (empty($data)) {
            return $employee;
        }
        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                $employee[$v['wxUserId']][$v['wxDepartmentId']] = $v;
            }
        }
        return $employee;
    }

    /**
     * 获取子账户信息.
     * @return array
     */
    protected function getUserData(array $phones = [], array $employee = [])
    {
        $this->userService = make(UserServiceInterface::class);
        $userData          = $this->userService->getUsersByPhone($phones, ['id', 'phone']);
        $user              = [];
        if (! empty($userData)) {
            foreach ($userData as $uk => $uv) {
                $user[$uv['phone']] = $uv['id'];
            }
            foreach ($employee as $ek => $ev) {
                if (empty($ev['mobile'])) {
                    continue;
                }
                $employee[$ek]['log_user_id'] = ! empty($user[$ev['mobile']]) ? $user[$ev['mobile']] : 0;
            }
        }
        return $employee;
    }

    /**
     * 获取联系人配置权限.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @return array
     */
    protected function getContactAuth(array $cropData, array $createEmployeeData = [])
    {
        //配置联系权限
        $followUser = $this->client->provider('externalContact')->app($cropData)->external_contact->getFollowUsers();
        if (empty($followUser['errcode']) && ! empty($followUser['follow_user'])) {
            foreach ($followUser['follow_user'] as $fk => $fv) {
                if (! empty($createEmployeeData[$fv])) {
                    $createEmployeeData[$fv]['contact_auth'] = ContactAuth::YES;
                }
            }
        }
        return $createEmployeeData;
    }

    /**
     * 处理员工部门.
     * @return bool
     */
    protected function createEmployee(array $createEmployeeData)
    {
        if (empty($createEmployeeData)) {
            return true;
        }
        //添加成员
        $createEmployeeResult = $this->workEmployeeService->createWorkEmployees($createEmployeeData);
        if (empty($createEmployeeResult)) {
            return false;
        }
        return true;
    }

    /**
     * 创建成员部门关系数据.
     * @return bool
     */
    protected function createEmployeeDepartment(array $corpIds, array $userIds, array $createEmployeeDepartmentData = [])
    {
        $createEmployeeDepartment = [];
        //获取刚添加的成员信息
        $wxUserEmployeeData = $this->workEmployeeService->getWorkEmployeesByCorpIdsWxUserId($corpIds, $userIds, ['id', 'wx_user_id']);
        if (empty($wxUserEmployeeData)) {
            return true;
        }
        foreach ($wxUserEmployeeData as $wxk => $wx) {
            if (! empty($createEmployeeDepartmentData[$wx['wxUserId']])) {
                foreach ($createEmployeeDepartmentData[$wx['wxUserId']] as $ck => $cv) {
                    $cv['employee_id']          = $wx['id'];
                    $createEmployeeDepartment[] = $cv;
                }
            }
        }
        //添加成员部门关系
        $result = $this->workEmployeeDepartmentService->createWorkEmployeeDepartments($createEmployeeDepartment);
        if (empty($result)) {
            return false;
        }

        return true;
    }

    /**
     * 获取部门ID.
     * @param $corpId
     * @return array
     */
    protected function getDepartmentIds($corpId)
    {
        $department     = [];
        $departmentData = $this->workDepartmentService->getWorkDepartmentsByCorpId($corpId);
        array_map(function ($item) use (&$department) {
            $department[$item['wxDepartmentId']] = $item;
        }, $departmentData);
        return $department;
    }

    /**
     * 获取公司信息.
     * @return array
     */
    protected function getCorpData(array $corpIds)
    {
        $corp              = [];
        $this->corpService = make(CorpServiceInterface::class);
        $corpData          = $this->corpService->getCorpsById($corpIds, ['wx_corpid', 'employee_secret', 'id']);
        if (empty($corpData)) {
            return $corp;
        }
        foreach ($corpData as $cdk => $cdv) {
            $corp[$cdv['id']] = [
                'corp_id' => $cdv['wxCorpid'],
                'secret'  => $cdv['employeeSecret'],
            ];
        }
        return $corp;
    }

    /**
     * 同步时间.
     * @param $corpIds
     * @return bool
     */
    protected function getSysTime($corpIds)
    {
        $synTimeData = $this->workUpdateTimeService->getWorkUpdateTimeByCorpIdType($corpIds, Type::EMPLOYEE);
        if (empty($synTimeData)) {
            foreach ($corpIds as $ck => $cv) {
                $createSynData[] = [
                    'corp_id'          => $cv,
                    'type'             => Type::EMPLOYEE,
                    'last_update_time' => date('Y-m-d H:i:s'),
                    'created_at'       => date('Y-m-d H:i:s'),
                ];
            }
        } else {
            $synCorpIds = [];
            foreach ($synTimeData as $stdk => $stdv) {
                $synCorpIds[] = $stdv['corpId'];
                if (in_array($stdv['corpId'], $corpIds)) {
                    $updateSynData[] = [
                        'id'               => $stdv['id'],
                        'last_update_time' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            $diffCorpIds = array_diff($corpIds, $synCorpIds);
            if (! empty($diffCorpIds)) {
                foreach ($diffCorpIds as $dck => $dcv) {
                    $createSynData[] = [
                        'corp_id'          => $dcv,
                        'type'             => Type::EMPLOYEE,
                        'last_update_time' => date('Y-m-d H:i:s'),
                        'created_at'       => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        if (! empty($createSynData)) {
            //创建同步数据
            $this->workUpdateTimeService->createWorkUpdateTimes($createSynData);
        }
        if (! empty($updateSynData)) {
            //修改同步数据
            $this->workUpdateTimeService->updateUpdateTimeByIds($updateSynData);
        }
        return true;
    }
}
