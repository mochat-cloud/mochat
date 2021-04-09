<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContact;

use App\Constants\WorkContactEmployee\Status;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 同步单一成员跟进客户信息
 * Class SynContactApply.
 */
class SynContactApply
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

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
     * @var
     */
    private $ecClient;

    /**
     * @AsyncQueueMessage(pool="contact")
     * @param array $employee 通讯录成员
     * @param int $corpId 企业授信ID
     * @param string $wxCorpid 企业微信ID
     */
    public function handle(array $employee, int $corpId, string $wxCorpid): void
    {
        $this->ecClient = $this->wxApp($wxCorpid, 'contact')->external_contact;
        // 微信拉取客户列表
        $wxContactList = $this->getWxContactList((int) $employee['id'], $employee['wxUserId']);

        if (empty($wxContactList)) {
            return;
        }
        $this->logger->error(sprintf('同步客户: %s [%s]', json_encode($employee), date('Y-m-d H:i:s')));
        ## 从数据表中当前员工所有的客户
        $oldContactList   = $this->getOldContactList((int) $employee['id'], $corpId);
        $deleteContactIds = [];
        if (! empty($oldContactList)) {
            foreach ($oldContactList as $oldContact) {
                in_array($oldContact['wxExternalUserid'], $wxContactList) || $deleteContactIds[] = $oldContact['id'];
            }
        }
        ## 删除员工不存在的客户
        $this->deleteContacts((int) $employee['id'], $deleteContactIds);

        $wxContactList          = array_chunk($wxContactList, 100);
        $synContactApplyByGroup = (new SynContactApplyByGroup());
        foreach ($wxContactList as $v) {
            $synContactApplyByGroup->handle($employee, $corpId, $wxCorpid, $v);
        }
    }

    /**
     * 从微信拉取客户列表.
     * @param int $employeeId 数据表用户ID
     * @param string $wxEmployeeId 微信用户ID
     * @return array 相应数组
     */
    private function getWxContactList(int $employeeId, string $wxEmployeeId): array
    {
        // 从微信拉取客户列表
        $contact = $this->ecClient->list($wxEmployeeId);
        if ($contact['errcode'] == 0) {
            return $contact['external_userid'];
        }
        if ($contact['errcode'] == 84061) { ## 无跟进客户
            $this->workContactEmployee->updateWorkContactEmployeesByEmployeeId($employeeId, ['status' => Status::REMOVE, 'deleted_at' => date('Y-m-d H:i:s')]);
            return [];
        }
        $this->logger->error(sprintf('同步客户-获取通讯录用户跟进的客户列表信息失败，error: %s [%s]', json_encode($contact), date('Y-m-d H:i:s')));
        return [];
    }

    /**
     * @param int $employeeId 员工ID
     * @param int $corpId 企业授信ID
     * @return array 相应数组
     */
    private function getOldContactList(int $employeeId, int $corpId): array
    {
        ## 员工-客户关联表信息
        $contactEmployeeList = $this->workContactEmployee->getWorkContactEmployeeByCorpIdEmployeeId($employeeId, $corpId, ['id', 'contact_id']);
        if (empty($contactEmployeeList)) {
            return [];
        }
        ## 员工表信息
        $contactList = $this->workContact->getWorkContactsById(array_column($contactEmployeeList, 'contactId'), ['id', 'wx_external_userid']);
        return $contactList ?? [];
    }

    /**
     * 删除员工客户信息.
     * @param int $employeeId 员工ID
     * @param array $contactIds 客户ID
     */
    private function deleteContacts(int $employeeId, array $contactIds)
    {
        $res = $this->workContactEmployee->updateWorkContactEmployeesByOtherId($employeeId, $contactIds, ['status' => Status::REMOVE, 'deleted_at' => date('Y-m-d H:i:s')]);
        if (! is_int($res)) {
            $this->logger->error(sprintf('同步客户-删除员工客户失败，error: %s [%s]', json_encode($res), date('Y-m-d H:i:s')));
        }
    }
}
