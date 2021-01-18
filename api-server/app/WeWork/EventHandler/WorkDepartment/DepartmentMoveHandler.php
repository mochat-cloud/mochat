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
 * 部门删除 - 事件回调.
 * @WeChatEventHandler(eventPath="event/change_contact/delete_party")
 * Class DepartmentMoveHandler
 */
class DepartmentMoveHandler extends AbstractEventHandler
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

    /**
     * @return null|mixed|string
     */
    public function process()
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($this->message)) {
            $this->logger->error('DepartmentMoveHandler->process同步删除部门message不能为空');
            return 'success';
        }
        //获取公司corpId
        $this->corpService = make(CorpServiceInterface::class);
        $corpIds           = $this->getCorpId();
        if (empty($corpIds)) {
            $this->logger->error('DepartmentMoveHandler->process同步删除部门corpIds不能为空');
            return 'success';
        }
        //部门
        $this->workDepartmentService = make(WorkDepartmentServiceInterface::class);
        $departments                 = $this->getDepartmentData($corpIds);
        if (empty($departments)) {
            $this->logger->info('DepartmentMoveHandler->process同步删除部门部门为空');
            return 'success';
        }
        //删除部门
        if (! empty($departments[$this->message['Id']])) {
            $result = $this->workDepartmentService->deleteWorkDepartment($departments[$this->message['Id']]);
            if (empty($result)) {
                $this->logger->error('DepartmentMoveHandler->process微信删除部门失败');
            }
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
     *  部门基础信息.
     * @param $corpIds
     */
    private function getDepartmentData($corpIds): array
    {
        $department     = [];
        $departmentData = $this->workDepartmentService->getWorkDepartmentsByCorpIds($corpIds, ['id', 'wx_department_id']);
        if (empty($departmentData)) {
            return [];
        }
        foreach ($departmentData as $dk => $dv) {
            $department[$dv['wxDepartmentId']] = $dv['id'];
        }
        return $department;
    }
}
