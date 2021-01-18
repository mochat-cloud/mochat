<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkDepartment;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\Framework\WeWork\WeWork;

/**
 * 部门管理-同步.
 *
 * Class SynLogic
 */
class SynLogic
{
    use DepartmentTrait;

    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @var WorkDepartmentServiceInterface
     */
    protected $workDepartmentService;

    /**
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function handle(array $corpIds): array
    {
        $this->logger = make(StdoutLoggerInterface::class);
        if (empty($corpIds)) {
            $this->logger->error('WorkDepartmentSynLogic->handle同步部门corpId不能为空');
            return [];
        }
        $this->client                = make(WeWork::class);
        $this->workDepartmentService = make(WorkDepartmentServiceInterface::class);
        $corpData                    = $this->getCorpData($corpIds);
        if (empty($corpData)) {
            $this->logger->error('WorkDepartmentSynLogic->handle同步部门corp不能为空');
            return [];
        }
        foreach ($corpData as $corpId => $cdv) {
            $wxDepartment = $this->client->provider('user')->app($cdv)->department->list();
            if (empty($wxDepartment['errcode']) && $wxDepartment['department']) {
                //获取部门ID
                $department = $this->getDepartmentIds($corpId);
                foreach ($wxDepartment['department'] as $key => $value) {
                    if (empty($department[$value['id']])) {
                        $createData[] = [
                            'wx_department_id' => $value['id'],
                            'corp_id'          => $corpId,
                            'name'             => $value['name'],
                            'wx_parentid'      => $value['parentid'],
                            'parent_id'        => ! empty($department[$value['parentid']]) ? $department[$value['parentid']]['id'] : 0,
                            'order'            => $value['order'],
                            'created_at'       => date('Y-m-d H:i:s'),
                        ];
                    }
                }
                if (! empty($createData)) {
                    //创建部门
                    $result = $this->workDepartmentService->createWorkDepartments($createData);
                    if ($result) {
                        //处理父部门
                        $parentDepartment = $this->getDepartmentIds($corpId);
                        $updateData       = $this->getDepartmentUpdateData($parentDepartment);
                        if (! empty($updateData)) {
                            $updateResult = $this->workDepartmentService->updateWorkDepartmentByIds($updateData);
                            if (empty($updateResult)) {
                                $this->logger->error('WorkDepartmentSynLogic->handle同步部门失败');
                                return [];
                            }
                            return [];
                        }
                    }
                }
            }
            unset($wxDepartment, $createData, $result, $parentDepartment, $updateData);
        }
        return [];
    }

    /**
     * 获取部门ID.
     * @param $corpId
     * @return array
     */
    private function getDepartmentIds($corpId)
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
    private function getCorpData(array $corpIds)
    {
        $this->corpService = make(CorpServiceInterface::class);
        $corpData          = $this->corpService->getCorpsById($corpIds, ['wx_corpid', 'employee_secret', 'id']);
        if (empty($corpData)) {
            return [];
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
     * 更新部门数据.
     * @param $parentDepartment
     * @return array
     */
    private function getDepartmentUpdateData($parentDepartment)
    {
        $updateData = [];
        array_map(function ($item) use (&$updateData, $parentDepartment) {
            //级别
            $relation = $this->getDepartmentRelation($item, $parentDepartment);
            $updateData[] = [
                'id'         => $item['id'],
                'updated_at' => date('Y-m-d H:i:s'),
                'path'       => $relation['path'],
                'level'      => $relation['level'],
                'parent_id'  => ! empty($parentDepartment[$item['wxParentid']]) ? $parentDepartment[$item['wxParentid']]['id'] : 0,
            ];
        }, $parentDepartment);
        return $updateData;
    }
}
