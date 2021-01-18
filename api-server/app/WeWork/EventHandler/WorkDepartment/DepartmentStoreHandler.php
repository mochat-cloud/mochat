<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\WeWork\EventHandler\WorkDepartment;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Logic\WorkDepartment\DepartmentTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 部门新增 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_contact/create_party")
 * Class EmployeeStoreHandler
 */
class DepartmentStoreHandler extends AbstractEventHandler
{
    use DepartmentTrait;

    /**
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @var WorkDepartmentServiceInterface
     */
    protected $workDepartmentService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function process()
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($this->message)) {
            $this->logger->error('DeparmentStoreHandler->process同步新增部门message不能为空');
            return 'success';
        }
        //获取公司corpId
        $this->corpService = make(CorpServiceInterface::class);
        $corpIds           = $this->getCorpId();
        if (empty($corpIds)) {
            $this->logger->error('DeparmentStoreHandler->process同步新增部门corpIds不能为空');
            return 'success';
        }
        //部门
        $this->workDepartmentService = make(WorkDepartmentServiceInterface::class);
        $departments                 = $this->getDepartmentData($corpIds);
        if (empty($departments)) {
            $this->logger->error('DeparmentStoreHandler->process同步新增部门部门不能为空');
            return 'success';
        }
        if (! empty($departments[$this->message['Id']])) {
            $this->logger->info('DeparmentStoreHandler->process同步新增部门部门' . $this->message['Id'] . '已经添加过啦');
            return 'success';
        }
        //添加部门
        $createDepartment = [
            'wx_department_id' => $this->message['Id'],
            'corp_id'          => current($corpIds),
            'name'             => $this->message['Name'],
            'parent_id'        => ! empty($departments[$this->message['ParentId']]['id']) ? $departments[$this->message['ParentId']]['id'] : 0,
            'wx_parentid'      => $this->message['ParentId'],
            'order'            => $this->message['Order'],
            'created_at'       => date('Y-m-d H:i:s'),
        ];
        $insertId = $this->workDepartmentService->createWorkDepartment($createDepartment);
        if (empty($insertId)) {
            $this->logger->error('DeparmentStoreHandler->process同步新增部门部门失败');
            return 'success';
        }
        //更新path和level
        $this->updateDepartment($insertId, $this->message['ParentId'], $departments);
        return 'success';
    }

    /**
     * 获取公司corpId.
     */
    private function getCorpId(): array
    {
        $corpData = $this->corpService->getCorpsByWxCorpId($this->message['ToUserName'], ['id']);
        if (empty($corpData)) {
            return [];
        }
        return [$corpData['id']];
    }

    /**
     * 部门基础信息.
     */
    private function getDepartmentData(array $corpIds): array
    {
        $department     = [];
        $departmentData = $this->workDepartmentService->getWorkDepartmentsByCorpIds($corpIds);
        if (empty($departmentData)) {
            return [];
        }
        array_map(function ($item) use (&$department) {
            $department[$item['wxDepartmentId']] = $item;
        }, $departmentData);
        return $department;
    }

    /**
     * 修改部门.
     * @param $departmentId
     * @param $wxParentId
     * @param $departments
     */
    private function updateDepartment($departmentId, $wxParentId, $departments): bool
    {
        $departmentData = [
            'id'         => $departmentId,
            'wxParentid' => $wxParentId,
        ];
        $relation   = $this->getDepartmentRelation($departmentData, $departments);
        $updateData = [
            'updated_at' => date('Y-m-d H:i:s'),
            'path'       => $relation['path'],
            'level'      => $relation['level'],
        ];
        $updateResult = $this->workDepartmentService->updateWorkDepartmentById($departmentId, $updateData);
        if (! $updateResult) {
            $this->logger->error('DeparmentStoreHandler->updateDepartment同步修改部门部门失败');
        }
        return true;
    }
}
