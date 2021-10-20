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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\WorkContact\Constants\AddWay;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Event\ContactWelcomeEvent;
use MoChat\App\WorkContact\Logic\Tag\SyncTagByContactLogic;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 根据员工同步客户.
 */
class SyncContactByEmployeeLogic
{
    /**
     * @Inject
     * @var CorpContract
     */
    private $corpService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    private $workContactService;

    /**
     * 通讯录 - 客户 - 轨迹互动.
     * @Inject
     * @var ContactEmployeeTrackContract
     */
    private $contactEmployeeTrackService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    private $workContactEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Inject
     * @var WeWorkFactory
     */
    private $weWorkFactory;

    /**
     * @Inject
     * @var SyncTagByContactLogic
     */
    private $syncTagByContactLogic;

    /**
     * @Inject()
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param int|string $corpId 企业授权ID
     * @param int|string $employeeId 跟进员工wxid
     * @param string $contactWxExternalUserId 客户wxid
     * @param array $message
     *
     * @return array
     */
    public function handle($corpId, $employeeId, string $contactWxExternalUserId, array $message): array
    {
        $data = [0, 0, []];
        $checkRes = $this->checkSyncData($corpId, $employeeId);
        if ($checkRes === false) {
            return $data;
        }

        // 开启事物
        Db::beginTransaction();
        try {
            $corp = $checkRes[0];
            $corpId = (int) $corp['id'];
            $employee = $checkRes[1];
            $employeeId = (int) $employee['id'];
            $employeeWxUserId = $employee['wxUserId'];
            [$contactId, $isNewContact, $followEmployee] = $this->getContact($corpId, $contactWxExternalUserId, $employeeWxUserId);

            if ($contactId === 0) {
                Db::rollBack();
                return [$contactId, $isNewContact, $employee];
            }

            // 更早的触发发送欢迎语，因为有效期只有20秒
            $state = isset($message['State']) ? $message['State'] : '';
            $welcomeCode = isset($message['WelcomeCode']) ? $message['WelcomeCode'] : '';
            $this->triggerContactWelcomeEvent($contactId, $employeeId, (string) $state, $isNewContact, $welcomeCode);

            // 创建客户员工关系
            $this->createContactEmployee($corpId, $contactId, $employeeId, $employee['name'], $followEmployee);

            if (isset($followEmployee['tags']) && ! empty($followEmployee['tags'])) {
                $this->syncTagByContactLogic->handle($corpId, $contactId, $employeeId, $followEmployee['tags']);
            }

            Db::commit();
            return [$contactId, $isNewContact, $employee];
        } catch (\Throwable $e) {
            Db::rollBack();
            // 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '创建客户信息失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            return $data;
        }
    }

    private function checkSyncData($corpId, $employeeId)
    {
        if (is_string($corpId)) {
            $corp = $this->corpService->getCorpByWxCorpId($corpId, ['id']);
        } else {
            $corp = $this->corpService->getCorpById($corpId, ['id']);
        }

        if (empty($corp)) {
            return false;
        }

        if (is_string($employeeId)) {
            $employee = $this->workEmployeeService->getWorkEmployeeByWxUserIdCorpId($employeeId, (int) $corp['id'], ['id', 'name', 'wx_user_id']);
        } else {
            $employee = $this->workEmployeeService->getWorkEmployeeById($employeeId, ['id', 'name', 'wx_user_id']);
        }

        if (empty($employee)) {
            return false;
        }

        return [$corp, $employee];
    }

    /**
     * 根据企业微信联系人id获取客户id.
     *
     * @return array
     */
    private function getContact(int $corpId, string $contactWxExternalUserId, string $employeeWxUserId)
    {
        $followEmployee = [];

        [$contactId, $isNewContact, $wxContactRes] = $this->createContact($corpId, $contactWxExternalUserId);

        if ($contactId !== 0) {
            $followEmployee = $this->getFollowEmployee($wxContactRes['follow_user'], $employeeWxUserId);
        }

        return [$contactId, $isNewContact, $followEmployee];
    }

    /**
     * 创建客户.
     *
     * @return array
     */
    private function createContact(int $corpId, string $contactWxExternalUserId): array
    {
        $contactId = 0;
        $isNewContact = 0;

        // 查询当前公司是否存在此客户
        $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corpId, $contactWxExternalUserId, ['id', 'deleted_at'], true);

        $wxContactRes = $this->weWorkFactory->getContactApp($corpId)->external_contact->get($contactWxExternalUserId);

        if ($wxContactRes['errcode'] !== 0) {
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户群详情错误', date('Y-m-d H:i:s'), $contactWxExternalUserId, json_encode($wxContactRes)));
            return [$contactId, $isNewContact, $wxContactRes];
        }

        $wxContact = $wxContactRes['external_contact'];
        $contractData = [
            'corp_id' => $corpId,
            'wx_external_userid' => $wxContact['external_userid'],
            'name' => $wxContact['name'],
            'avatar' => ! empty($wxContact['avatar']) ? $wxContact['avatar'] : '',
            'type' => $wxContact['type'],
            'gender' => $wxContact['gender'],
            'unionid' => isset($wxContact['unionid']) ? $wxContact['unionid'] : '',
            'position' => isset($wxContact['position']) ? $wxContact['position'] : '',
            'corp_name' => isset($wxContact['corp_name']) ? $wxContact['corp_name'] : '',
            'corp_full_name' => isset($wxContact['corp_full_name']) ? $wxContact['corp_full_name'] : '',
            'external_profile' => isset($wxContact['external_profile']) ? json_encode($wxContact['external_profile']) : json_encode([]),
            'business_no' => isset($wxContact['business_no']) ? $wxContact['business_no'] : '',
        ];

        // 无结果则创建
        if (empty($contact)) {
            $createContractData = $contractData;
            $createContractData['created_at'] = date('Y-m-d H:i:s');
            $contactId =  $this->workContactService->createWorkContact($createContractData);
            $isNewContact = 1;
        } else {
            $updateContractData = $contractData;
            $updateContractData['updated_at'] = date('Y-m-d H:i:s');
            $updateContractData['deleted_at'] = null;
            $contactId = (int) $contact['id'];
            $this->workContactService->updateWorkContactById($contactId, $updateContractData, true);
        }

        if (! empty($contact) && ! empty($contact['deletedAt'])) {
            $isNewContact = 1;
        }

        return [$contactId, $isNewContact, $wxContactRes];
    }

    /**
     * @return array|mixed
     */
    private function getFollowEmployee(array $followUser, string $employeeWxUserId)
    {
        $followEmployee = [];
        foreach ($followUser as $follow) {
            if ($follow['userid'] == $employeeWxUserId) {
                $followEmployee = $follow;
            }
        }
        return $followEmployee;
    }

    /**
     * 创建跟进员工中间表信息.
     */
    private function createContactEmployee(int $corpId, int $contactId, int $employeeId, string $employeeName, array $followEmployee)
    {
        if (empty($followEmployee)) {
            return;
        }

        // 查询当前用户与客户是否存在关联信息
        $workContactEmployee = $this->workContactEmployeeService->findWorkContactEmployeeByOtherIds($employeeId, $contactId, ['id', 'deleted_at'], true);

        $addWay = isset($followEmployee['add_way']) ? $followEmployee['add_way'] : 0;
        // 组织客户与企业用户关联表信息
        $contractEmployeeData = [
            'employee_id' => $employeeId,
            'contact_id' => $contactId,
            'remark' => isset($followEmployee['remark']) ? $followEmployee['remark'] : '',
            'description' => isset($followEmployee['description']) ? $followEmployee['description'] : '',
            'remark_corp_name' => isset($followEmployee['remark_corp_name']) ? $followEmployee['remark_corp_name'] : '',
            'remark_mobiles' => isset($followEmployee['remark_mobiles']) ? json_encode($followEmployee['remark_mobiles']) : json_encode([]),
            'add_way' => $addWay,
            'oper_userid' => isset($followEmployee['oper_userid']) ? $followEmployee['oper_userid'] : '',
            'state' => isset($followEmployee['state']) ? $followEmployee['state'] : '',
            'corp_id' => $corpId,
            'status' => 1, // 正常
            'create_time' => isset($followEmployee['createtime']) ? date('Y-m-d H:i:s', $followEmployee['createtime']) : '',
        ];

        if (empty($workContactEmployee)) {
            $createContractEmployeeData = $contractEmployeeData;
            $createContractEmployeeData['created_at'] =  date('Y-m-d H:i:s');
            $this->workContactEmployeeService->createWorkContactEmployee($createContractEmployeeData);
        } else {
            $updateContractEmployeeData = $contractEmployeeData;
            $updateContractEmployeeData['updated_at'] =  date('Y-m-d H:i:s');
            $updateContractEmployeeData['deleted_at'] = null;
            $this->workContactEmployeeService->updateWorkContactEmployeeById((int) $workContactEmployee['id'], $updateContractEmployeeData, true);
        }

        if (empty($workContactEmployee) || ! empty($workContactEmployee['deletedAt'])) {
            $this->createContactTrack($corpId, $contactId, $employeeId, $employeeName, $addWay);
        }
    }

    /**
     * 创建客户轨迹.
     */
    private function createContactTrack(int $corpId, int $contactId, int $employeeId, string $employeeName, int $addWay)
    {
        // 创建客户事件
        $contactEmployeeTrackData = [
            'employee_id' => $employeeId,
            'contact_id' => $contactId,
            'event' => Event::CREATE,
            'content' => sprintf('客户通过%s添加企业成员【%s】', AddWay::getMessage($addWay), $employeeName),
            'corp_id' => $corpId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        // 记录日志
        $this->contactEmployeeTrackService->createContactEmployeeTrack($contactEmployeeTrackData);
    }

    private function triggerContactWelcomeEvent(
        int $contactId,
        int $employeeId,
        string $state,
        int $isNewContact,
        string $welcomeCode
    )
    {
        $contact = $this->workContactService->getWorkContactById($contactId);

        if (empty($contact)) {
            return;
        }

        $contact['employeeId'] = $employeeId;
        $contact['state'] = $state;
        $contact['isNew'] = $isNewContact;
        $contact['welcomeCode'] = $welcomeCode;

        go(function () use ($contact) {
            $this->eventDispatcher->dispatch(new ContactWelcomeEvent($contact));
        });
    }
}
