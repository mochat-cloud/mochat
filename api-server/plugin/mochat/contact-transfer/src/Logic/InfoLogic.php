<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkTransferLogContract;

/**
 * Class InfoLogic.
 */
class InfoLogic
{
    /**
     * @Inject
     * @var WorkTransferLogContract
     */
    protected $workTransferLogService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * 获取待分配客户列表.
     * @return mixed
     */
    public function getBeAssignedContactList(array $params)
    {
        //消息
        $this->workMessageService = make(WorkMessageContract::class, [$params['corpId']]);

        $contactEmployees = $this->workContactEmployeeService->getWorkContactEmployeesByRemarkAndTimeLimit($params['addTimeStart'], $params['addTimeEnd'], $params['contactName']);

        if (count($params['employeeId'])) {
            $temp = [];
            foreach ($contactEmployees as $contactEmployee) {
                if (in_array($contactEmployee['employeeId'], $params['employeeId'])) {
                    $temp[] = $contactEmployee;
                }
            }
            $contactEmployees = $temp;
        }

        $res = [];
        $wayTexts = [
            0 => '未知来源',
            1 => '扫描二维码',
            2 => '搜索手机号',
            3 => '名片分享',
            4 => '群聊',
            5 => '手机通讯录',
            6 => '微信联系人',
            7 => '来自微信的添加好友申请',
            8 => '安装第三方应用时自动添加的客服人员',
            9 => '搜索邮箱',
            201 => '内部成员共享',
            202 => '管理员/负责人分配',
            1001 => '渠道活码',
            1002 => '自动拉群',
            1003 => '裂变引流',
        ];

        foreach ($contactEmployees as $contactEmployee) {
            $employee = $this->workEmployeeService->getWorkEmployeeByIdWithTrashed($contactEmployee['employeeId']);
            $tags = $this->workContactTagPivotService->getWorkContactTagPivotsByContactIdEmployeeId([$contactEmployee['contactId']], $contactEmployee['employeeId']);
            $tagName = [];
            foreach ($tags as $tag) {
                $tagName[] = $this->workContactTagService->getWorkContactTagById($tag['contactTagId'])['name'];
            }

            $contact = $this->workContactService->getWorkContactById($contactEmployee['contactId']);
//            $res[] = [
//                'employee' => $employee,
//                'contact' => $contact,
//                'contact_employee' => $contactEmployee,
//            ];

            $lastMsg = $this->workMessageService->getLastMessageByEmployeeWxIdAndContactWxId($employee['wxUserId'], $contactEmployee['operUserid']);
            $lastMsg = $lastMsg ? date('Y-m-d H:i', (int) ((int) $lastMsg[0]->msg_time / 1000)) : '';
            $res[] = [
                'contactId' => $contactEmployee['contactId'],
                'employeeId' => $employee['id'],
                'contactWxId' => $contact['wxExternalUserid'],
                'employeeWxId' => $employee['wxUserId'],
                'contactName' => $contactEmployee['remark'],
                'nickName' => $contact['name'],
                'corpName' => $contact['corpName'],
                'employeeName' => $employee['name'],
                'tags' => $tagName,
                'transferState' => $this->workTransferLogService->getLogStateByCorpId($params['corpId'], $contact['wxExternalUserid'], $employee['wxUserId']),
                'addTime' => date('Y-m-d H:i', strtotime($contactEmployee['createTime'])),
                'lastMsgTime' => $lastMsg,
                'addWay' => $wayTexts[$contactEmployee['addWay']],
            ];
        }

        return $res;
    }
}
