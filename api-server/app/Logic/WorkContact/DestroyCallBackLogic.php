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

use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 删除企业客户回调.
 *
 * Class DestroyCallBackLogic
 */
class DestroyCallBackLogic
{
    /**
     * 客户表.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $contact;

    /**
     * 员工表.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $employee;

    /**
     * 员工 - 客户关联.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $contactEmployee;

    /**
     * 客户 - 标签关联.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivot;

    /**
     * @param $status
     */
    public function handle(array $wxResponse, $status): void
    {
        //如果没有员工微信id和客户微信id
        if (! isset($wxResponse['UserID'], $wxResponse['ExternalUserID'])) {
            return;
        }

        //查询客户id,查询员工id
        [$contactId,$employeeId] = $this->employeeAndContactData($wxResponse['UserID'], $wxResponse['ExternalUserID']);
        if ($contactId === 0 && $employeeId === 0) {
            return;
        }

        //查询是否有该员工与客户的对应关系，如果有，删除
        $contactEmployee = $this->contactEmployee->getWorkContactEmployeeByOtherId($employeeId, $contactId, ['id']);

        if (! empty($contactEmployee)) {
            //更新status为删除
            $data['status'] = $status;
            $updateRes      = $this->contactEmployee->updateWorkContactEmployeeById((int) $contactEmployee['id'], $data);
            if (! $updateRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '更新删除状态失败');
            }
            //删除对应关系
            $deleteRes = $this->contactEmployee->deleteWorkContactEmployee((int) $contactEmployee['id']);
            if (! $deleteRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除企业客户员工回调失败');
            }
        }

        //查询员工对客户打的标签
        $tag = $this->contactTagPivot->getWorkContactTagPivotsByOtherId($contactId, $employeeId, ['id']);
        if (! empty($tag)) {
            $ids = array_column($tag, 'id');
            //删除客户标签
            $deleteTagRes = $this->contactTagPivot->deleteWorkContactTagPivots($ids);
            if (! $deleteTagRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除企业客户标签回调失败');
            }
        }
    }

    /**
     * 获取员工与客户ID.
     * @param string $wxUserId ...
     * @param string $wxContactUserId ...
     * @return array ...
     */
    protected function employeeAndContactData(string $wxUserId, string $wxContactUserId): array
    {
        $contactId   = 0;
        $employeeId  = 0;
        $contactInfo = $this->contact->getWorkContactByWxExternalUserId($wxContactUserId, ['id']);
        if (! empty($contactInfo)) {
            $contactId = $contactInfo['id'];
        }
        $employeeInfo = $this->employee->getWorkEmployeeByWxUserId($wxUserId, ['id']);
        if (! empty($employeeInfo)) {
            $employeeId = $employeeInfo['id'];
        }

        return [$contactId, $employeeId];
    }
}
