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
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

/**
 * 部门修改 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_contact/update_party")
 * Class DepartmentUpdateHandler
 */
class DepartmentUpdateHandler extends AbstractEventHandler
{
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
            $this->logger->error('DepartmentUpdateHandler->process同步修改部门message不能为空');
            return 'success';
        }
        //获取公司corpId
        $this->corpService = make(CorpServiceInterface::class);
        $corpIds           = $this->getCorpId();
        //部门
        $this->workDepartmentService = make(WorkDepartmentServiceInterface::class);
        $departments                 = $this->getDepartmentData($corpIds);
        if (empty($departments)) {
            $this->logger->error('DepartmentUpdateHandler->process同步修改部门corpIds不能为空');
            return 'success';
        }
        //不存在部门
        if (empty($departments[$this->message['Id']])) {
            $this->logger->error('DepartmentUpdateHandler->process同步修改部门部门不能为空');
            return 'success';
        }
        //所属父部门（当发生改变时才会传递）
        $parentId = $departments[$this->message['Id']]['parentId'];
        if (! empty($this->message['ParentId'])) {
            $parentId = ! empty($departments[$this->message['ParentId']]) ? $departments[$this->message['ParentId']]['id'] : 0;
        }
        $updateDepartment = [
            'id'               => $departments[$this->message['Id']]['id'],
            'wx_department_id' => $this->message['Id'],
            'corp_id'          => current($corpIds),
            'name'             => ! empty($this->message['Name']) ? $this->message['Name'] : '',
            'parent_id'        => $parentId,
            'wx_parentid'      => ! empty($this->message['ParentId']) ? $this->message['ParentId'] : $departments[$this->message['Id']]['parentId'],
            'order'            => ! empty($this->message['Order']) ? $this->message['Order'] : $departments[$this->message['Id']]['order'],
            'updated_at'       => date('Y-m-d H:i:s'),
        ];
        $result = $this->workDepartmentService->updateWorkDepartmentById($departments[$this->message['Id']]['id'], $updateDepartment);
        if (empty($result)) {
            $this->logger->error('DepartmentUpdateHandler->process微信修改部门失败');
            return 'success';
        }
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
     * @param $corpIds
     */
    private function getDepartmentData($corpIds): array
    {
        $department     = [];
        $departmentData = $this->workDepartmentService->getWorkDepartmentsByCorpIds($corpIds);
        if (empty($departmentData)) {
            return [];
        }
        foreach ($departmentData as $dk => $dv) {
            $department[$dv['wxDepartmentId']] = $dv;
        }
        return $department;
    }
}
