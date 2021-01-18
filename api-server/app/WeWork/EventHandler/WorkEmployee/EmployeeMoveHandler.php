<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\WeWork\EventHandler\WorkEmployee;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 成员删除 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_contact/delete_user")
 * Class EmployeeStoreHandler
 */
class EmployeeMoveHandler extends AbstractEventHandler
{
    /**
     * @var WorkEmployeeServiceInterface;
     */
    protected $workEmployeeService;

    /**
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @var WorkEmployeeDepartmentServiceInterface
     */
    protected $workEmployeeDepartmentService;

    /**
     * @var WorkContactEmployeeServiceInterface
     */
    protected $workContactEmployeeService;

    /**
     * @var WorkContactTagPivotServiceInterface
     */
    protected $workContactTagPivotService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function process()
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($this->message)) {
            $this->logger->error('EmployeeMoveHandler->process同步删除成员message不能为空');
            return 'success';
        }
        $this->workEmployeeService = make(WorkEmployeeServiceInterface::class);
        //获取公司corpId
        $this->corpService = make(CorpServiceInterface::class);
        $corpData          = $this->corpService->getCorpsByWxCorpId($this->message['ToUserName'], ['id']);
        if (empty($corpData)) {
            $this->logger->error('EmployeeMoveHandler->process同步删除成员corp不能为空');
            return 'success';
        }
        $employeeData = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserId((string) $corpData['id'], (string) $this->message['UserID'], ['id']);
        if (empty($employeeData)) {
            $this->logger->error('EmployeeMoveHandler->process同步删除成员employee不能为空');
            return 'success';
        }
        $employeeId                          = current($employeeData)['id'];
        $this->workEmployeeDepartmentService = make(WorkEmployeeDepartmentServiceInterface::class);
        $employeeDepartment                  = $this->workEmployeeDepartmentService->getWorkEmployeeDepartmentsByEmployeeId($employeeId, ['id']);
        //成员下客户
//        $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdEmployeeId((int) $employeeId, (int) $corpData['id'], ['id', 'contact_id']);
//        if (! empty($contactEmployee)) {
//            foreach ($contactEmployee as $cek => $cev) {
//                $contactEmployeeIds[] = $cev['id'];
//                $contactIds[]         = $cev['contactId'];
//            }
//            if (! empty($contactIds)) {
//                //客户标签
//                $contactTag = $this->workContactTagPivotService->getWorkContactTagPivotsByContactIdEmployeeId($contactIds, (int) $employeeId, ['id']);
//                if (! empty($contactTag)) {
//                    foreach ($contactTag as $ctk => $ctv) {
//                        $contactTagIds[] = $ctv['id'];
//                    }
//                }
//            }
//        }
        //开启事务
        Db::beginTransaction();
        try {
            //删除成员
            $this->workEmployeeService->deleteWorkEmployee($employeeId);
            if (! empty($employeeDepartment)) {
                foreach ($employeeDepartment as $edk => $edv) {
                    $employeeDepartmentIds[] = $edv['id'];
                }
                //删除成员和部门关联
                $this->workEmployeeDepartmentService->deleteWorkEmployeeDepartments($employeeDepartmentIds);
//                            //删除客户和成员关联
//                            if (! empty($contactIds)) {
//                                $this->workContactEmployeeService->deleteWorkContactEmployees($contactEmployeeIds);
//                            }
//                            //删除客户成员和标签关联
//                            if (! empty($contactTagIds)) {
//                                $this->workContactTagPivotService->deleteWorkContactTagPivots($contactTagIds);
//                            }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('EmployeeMoveHandler->process微信删除成员异常');
            return 'success';
        }
    }
}
