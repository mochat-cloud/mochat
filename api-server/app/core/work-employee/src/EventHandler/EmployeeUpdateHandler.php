<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\EventHandler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkDepartment\Contract\WorkDepartmentContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeDepartmentContract;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 成员修改 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_contact/update_user")
 * Class EmployeeUpdateHandler
 */
class EmployeeUpdateHandler extends AbstractEventHandler
{
    /**
     * @var WorkEmployeeContract;
     */
    protected $workEmployeeService;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @var WorkEmployeeDepartmentContract
     */
    protected $workEmployeeDepartmentService;

    /**
     * @var WorkDepartmentContract
     */
    protected $workDepartmentService;

    /**
     * @var UserContract
     */
    protected $userService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function process()
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($this->message)) {
            $this->logger->error('EmployeeUpdateHandler->process同步修改成员message不能为空');
            return 'success';
        }
        //获取公司corpId
        $this->corpService = make(CorpContract::class);
        $corpIds = $this->getCorpId();
        if (empty($corpIds)) {
            $this->logger->error('EmployeeUpdateHandler->process同步修改成员corpIds不能为空');
            return 'success';
        }
        $this->workEmployeeService = make(WorkEmployeeContract::class);
        //成员基础信息
        $employeeData = $this->workEmployeeService->getWorkEmployeesByCorpIdsWxUserId($corpIds, [$this->message['UserID']]);
        if (empty($employeeData)) {
            $this->logger->error('EmployeeUpdateHandler->process同步修改成员employeeData不能为空');
            return 'success';
        }
        $employee = end($employeeData);
        //成员和部门关系
        $this->workEmployeeDepartmentService = make(WorkEmployeeDepartmentContract::class);
        //部门
        $this->workDepartmentService = make(WorkDepartmentContract::class);
        $departmentData = $this->getDepartmentData($corpIds, $employee['id']);
        if (empty($departmentData)) {
            $this->logger->error('EmployeeUpdateHandler->process同步修改成员departmentData不能为空');
            return 'success';
        }
        $department = ! empty($departmentData['department']) ? $departmentData['department'] : [];
        $edepartment = ! empty($departmentData['edepartment']) ? $departmentData['edepartment'] : [];
        $updateEmployee = $this->getEmployeeData($employee);
        //主部门
        if (! empty($this->message['MainDepartment']) && $employee['wxMainDepartmentId'] != $this->message['MainDepartment']) {
            $updateEmployee['main_department_id'] = ! empty($department[$this->message['MainDepartment']]) ? $department[$this->message['MainDepartment']] : $employee['mainDepartmentId'];
            $updateEmployee['wx_main_department_id'] = $this->message['MainDepartment'];
        }
        //部门
        $isLeaderInDept = ! empty($this->message['IsLeaderInDept']) ? $this->message['IsLeaderInDept'] : '';
        $orders = ! empty($this->message['Order']) ? $this->message['Order'] : '';
        if (! empty($this->message['Department']) && ! empty($edepartment)) {
            $wxDepartment = explode(',', $this->message['Department']);
            if (count($wxDepartment) == 1) {
                //当有且只有一个部门的时候 表示该部门也是主部门
                //不知道为什么 当部门只有1个的时候 他的MainDepartment 事件儿不返回 不知是否是api触发的缘故 总之做一层兼容保险下
                $mainDepartmentId = current($wxDepartment);
                if ($employee['wxMainDepartmentId'] != $mainDepartmentId) {
                    $updateEmployee['main_department_id'] = ! empty($department[$mainDepartmentId]) ? $department[$mainDepartmentId] : $employee['mainDepartmentId'];
                    $updateEmployee['wx_main_department_id'] = $mainDepartmentId;
                }
            }
            //解除成员部门绑定关系
            foreach ($edepartment as $edk => $edv) {
                if (! in_array($edk, $wxDepartment)) {
                    $deleteEmployeeDepartmentData[] = [$edv];
                }
            }
            foreach ($wxDepartment as $wxk => $wxv) {
                //是否成员和部门关联
                if (! empty($edepartment[$wxv])) {
                    //部门内是否为上级
                    if (! empty($edepartment[$wxv]['isLeaderInDept']) && $edepartment[$wxv]['isLeaderInDept'] != $isLeaderInDept[$wxk]) {
                        $updateEmployeeDepartment[] = [
                            'id' => $edepartment[$wxv]['id'],
                            'is_leader_in_dept' => ! empty($isLeaderInDept[$wxk]) ? $isLeaderInDept[$wxk] : 0,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                } else {
                    //绑定成员和部门关系
                    if (empty($createEmployeeDepartment[$employee['id']])) {
                        $createEmployeeDepartment[$employee['id']] = [
                            'employee_id' => $employee['id'],
                            'is_leader_in_dept' => ! empty($isLeaderInDept[$wxk]) ? $isLeaderInDept[$wxk] : 0,
                            'department_id' => ! empty($department[$wxv]) ? $department[$wxv] : 0,
                            'order' => ! empty($orders[$wxk]) ? $orders[$wxk] : 0,
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }
        //开启事务
        Db::beginTransaction();
        try {
            // 更新成员
            if (! empty($updateEmployee)) {
                $this->workEmployeeService->updateWorkEmployeeById($employee['id'], $updateEmployee);
                // 员工离职子账户禁用
                if ($updateEmployee['status'] === 2 && ! empty($updateEmployee['mobile'])) {
                    $this->userService = make(UserContract::class);
                    $this->userService->updateUserStatusByPhone($updateEmployee['mobile'], 2);
                }
            }
            // 添加成员部门关系
            if (! empty($createEmployeeDepartment)) {
                $this->workEmployeeDepartmentService->createWorkEmployeeDepartments($createEmployeeDepartment);
            }

            // 删掉成员绑定的部门关系
            if (! empty($deleteEmployeeDepartmentData)) {
                $this->workEmployeeDepartmentService->deleteWorkEmployeeDepartments($deleteEmployeeDepartmentData);
            }
            // 编辑成员绑定部门关系
            if (! empty($updateEmployeeDepartment)) {
                $this->workEmployeeDepartmentService->batchUpdateByIds($updateEmployeeDepartment);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('EmployeeUpdateHandler->process同步修改成员失败');
            return 'success';
        }
    }

    /**
     * 获取公司corpId.
     */
    protected function getCorpId(): array
    {
        $corpData = $this->corpService->getCorpsByWxCorpId($this->message['ToUserName'], ['id']);
        if (empty($corpData)) {
            return [];
        }
        return [$corpData['id']];
    }

    /**
     * 部门基础信息.
     * @param $corpIds
     * @param $employeeId
     */
    protected function getDepartmentData($corpIds, $employeeId): array
    {
        $employeeDepartmentData = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeId($employeeId);
        if (! empty($employeeDepartmentData)) {
            foreach ($employeeDepartmentData as $edk => $edv) {
                $employeeDepartment[$edv['departmentId']] = $edv;
            }
        }
        $departmentData = $this->workDepartmentService->getWorkDepartmentsByCorpIds($corpIds, ['id', 'wx_department_id']);
        if (empty($departmentData)) {
            return [];
        }
        $edepartment = [];
        foreach ($departmentData as $dk => $dv) {
            $department[$dv['wxDepartmentId']] = $dv['id'];
            //部门
            if (! empty($employeeDepartment[$dv['id']])) {
                $edepartment[$dv['wxDepartmentId']] = $employeeDepartment[$dv['id']]['id'];
            }
        }

        return ['department' => $department, 'edepartment' => $edepartment];
    }

    /**
     * 成员基础数据.
     * @param $employee
     * @throws \League\Flysystem\FileExistsException
     * @return array
     */
    protected function getEmployeeData($employee)
    {
//        if (! empty($this->message['Avatar'])) {
//            $pathAvatarFileName = 'employee/avatar_' . microtime(true) * 10000 . '.png';
//            $thumbAvatar        = $this->message['Avatar'];
//            if (strpos($this->message['Avatar'], '/0') !== false) {
//                $thumbAvatar = substr($this->message['Avatar'], 0, strpos($this->message['Avatar'], '/0')) . '/100';
//            }
//            $pathThumAvatarFileName = 'employee/thumb_avatar_' . strval(microtime(true) * 10000) . '_' . uniqid() . '.png';
//            $ossData                = [
//                [$this->message['Avatar'], $pathAvatarFileName],
//                [$thumbAvatar, $pathThumAvatarFileName],
//            ];
//            file_upload_queue($ossData);
//        }
        $logUserId = $employee['logUserId'];
        if (! empty($this->message['Mobile']) && $this->message['Mobile'] != $employee['mobile']) {
            //子账户关联
            $logUserId = $this->getUserData($this->message['Mobile']);
        }
        return [
            'id' => $employee['id'],
            'name' => ! empty($this->message['Name']) ? $this->message['Name'] : $employee['name'],
            'mobile' => ! empty($this->message['Mobile']) ? $this->message['Mobile'] : $employee['mobile'],
            'position' => ! empty($this->message['Position']) ? $this->message['Position'] : $employee['position'],
            'gender' => ! empty($this->message['Gender']) ? $this->message['Gender'] : $employee['gender'],
            'email' => ! empty($this->message['Email']) ? $this->message['Email'] : $employee['email'],
            'avatar' => ! empty($this->message['Avatar']) ? $this->message['Avatar'] : $employee['avatar'],
            'thumb_avatar' => ! empty($this->message['ThumbAvatar']) ? $this->message['ThumbAvatar'] : $employee['thumbAvatar'],
            'telephone' => ! empty($this->message['Telephone']) ? $this->message['Telephone'] : $employee['telephone'],
            'alias' => ! empty($this->message['Alias']) ? $this->message['Alias'] : $employee['alias'],
            'extattr' => ! empty($this->message['ExtAttr']) ? json_encode($this->message['ExtAttr']) : $employee['extattr'],
            'status' => ! empty($this->message['Status']) ? $this->message['Status'] : $employee['status'],
            'address' => ! empty($this->message['Address']) ? $this->message['Address'] : $employee['address'],
            'log_user_id' => $logUserId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 获取子账户信息.
     * @return int
     */
    protected function getUserData(string $phone)
    {
        if (! empty($phone)) {
            $this->userService = make(UserContract::class);
            $userData = $this->userService->getUserByPhone($phone, ['id']);
            if (! empty($userData->id)) {
                return $userData->id;
            }
            return 0;
        }
        return 0;
    }
}
