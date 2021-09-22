<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;

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
     * @var WorkContactContract
     */
    private $contact;

    /**
     * @Inject()
     * @var CorpContract
     */
    private $corpService;

    /**
     * 员工表.
     * @Inject
     * @var WorkEmployeeContract
     */
    private $employee;

    /**
     * 员工 - 客户关联.
     * @Inject
     * @var WorkContactEmployeeContract
     */
    private $contactEmployee;

    /**
     * 客户 - 标签关联.
     * @Inject
     * @var WorkContactTagPivotContract
     */
    private $contactTagPivot;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    private $workFissionContact;

    /**
     * @param $status
     */
    public function handle(array $wxResponse, $status): void
    {
        // 如果没有员工微信id和客户微信id
        if (! isset($wxResponse['UserID'], $wxResponse['ExternalUserID'])) {
            return;
        }

        // 查询客户id,查询员工id
        [$contactId, $employeeId] = $this->employeeAndContactData($wxResponse['ToUserName'], $wxResponse['UserID'], $wxResponse['ExternalUserID']);
        if ($contactId === 0 || $employeeId === 0) {
            return;
        }

        // 查询是否有该员工与客户的对应关系，如果有，删除
        $contactEmployee = $this->contactEmployee->getWorkContactEmployeeByOtherId($employeeId, $contactId, ['id']);

        if (! empty($contactEmployee)) {
            // 更新status为删除
            $data['status'] = $status;
            $updateRes = $this->contactEmployee->updateWorkContactEmployeeById((int) $contactEmployee['id'], $data);
            if (! $updateRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '更新删除状态失败');
            }
            //删除对应关系
            $deleteRes = $this->contactEmployee->deleteWorkContactEmployee((int) $contactEmployee['id']);
            if (! $deleteRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除企业客户员工回调失败');
            }
        }

        // 查询员工对客户打的标签
        $tag = $this->contactTagPivot->getWorkContactTagPivotsByOtherId($contactId, $employeeId, ['id']);
        if (! empty($tag)) {
            $ids = array_column($tag, 'id');
            //删除客户标签
            $deleteTagRes = $this->contactTagPivot->deleteWorkContactTagPivots($ids);
            if (! $deleteTagRes) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除企业客户标签回调失败');
            }
        }

        // 无跟进员工时，真正删除客户
        $contactEmployees = $this->contactEmployee->getWorkContactEmployeesByContactId($contactId, ['id']);
        if (empty($contactEmployees)) {
            $this->contact->deleteWorkContact($contactId);
        }
    }

    /**
     * 获取员工与客户ID.
     * @param string $wxUserId ...
     * @param string $wxContactUserId ...
     * @return array ...
     */
    protected function employeeAndContactData(string $wxCorpId, string $wxUserId, string $wxContactUserId): array
    {
        $contactId = 0;
        $employeeId = 0;
        $contactInfo = $this->contact->getWorkContactByWxExternalUserId($wxContactUserId, ['id', 'unionid']);
        if (! empty($contactInfo)) {
            $contactId = $contactInfo['id'];
            ## 任务宝-裂变用户处理
            $this->handleFission($contactInfo['unionid']);
        }

        $corp = $this->corpService->getCorpByWxCorpId($wxCorpId, ['id']);

        if (empty($corp)) {
            return [$contactId, $employeeId];
        }

        $employeeInfo = $this->employee->getWorkEmployeeByWxUserIdCorpId($wxUserId, (int) $corp['id'], ['id']);
        if (! empty($employeeInfo)) {
            $employeeId = $employeeInfo['id'];
        }

        return [$contactId, $employeeId];
    }

    /**
     * // TODO 需调整为事件处理
     * 任务宝-裂变用户处理.
     * @param string $unionId
     *
     * @return array
     */
    protected function handleFission($unionId): array
    {
        $fissionContact = $this->workFissionContact->getWorkFissionContactByUnionId($unionId, ['id', 'contact_superior_user_parent']);
        if (! empty($fissionContact)) {
            $this->workFissionContact->updateWorkFissionContactById((int) $fissionContact['id'], ['loss' => 1]);
            if ($fissionContact['contactSuperiorUserParent'] > 0) {
                $parent = $this->workFissionContact->getWorkFissionContactById((int) $fissionContact['contactSuperiorUserParent'], ['id', 'invite_count']);
                $this->workFissionContact->updateWorkFissionContactById((int) $fissionContact['contactSuperiorUserParent'], ['invite_count' => $parent['inviteCount'] - 1]);
            }
        }
        return [];
    }
}
