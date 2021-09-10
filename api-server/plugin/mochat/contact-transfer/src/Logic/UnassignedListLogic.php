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
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class UnassignedListLogic.
 */
class UnassignedListLogic
{
    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

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
     * 获取离职待分配客户列表.
     * @param array $params
     * @return array
     */
    public function getUnassignedList($params)
    {
        $this->workMessageService = make(WorkMessageContract::class, [$params['corpId']]);
        $unassignedList = $this->workUnassignedService->getWorkUnassignedByCorpId([$params['corpId']]);

        $lastTime = '无数据';
        if (count($unassignedList)) {
            $lastTime = $unassignedList[0]['createdAt'];
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
        ];

        foreach ($unassignedList as $item) {
            $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdAndWxUserId($params['corpId'], $item['handoverUserid']);
            $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($params['corpId'], $item['externalUserid']);
            $contactEmployee = $this->workContactEmployeeService->findWorkContactEmployeeByOtherIds($employee['id'], $contact['id']);
            $tags = $this->workContactTagPivotService->getWorkContactTagPivotsByContactIdEmployeeId([$contactEmployee['contactId']], $contactEmployee['employeeId']);
            $tagName = [];
            foreach ($tags as $tag) {
                $tagName[] = $this->workContactTagService->getWorkContactTagById($tag['contactTagId'])['name'];
            }

            $lastMsg = $this->workMessageService->getLastMessageByEmployeeWxIdAndContactWxId($employee['wxUserId'], $contactEmployee['operUserid']);
            $lastMsg = $lastMsg ? date('Y-m-d H:i', (int) ((int) $lastMsg[0]->msg_time / 1000)) : '';

//            $res[] = [
//                'unassigned' => $item,
//                'employee' => $employee,
//                'contact' => $contact,
//                'contactEmployee' => $contactEmployee,
//                'tags' => $tagName
//            ];

            $res[] = [
                'contactId' => $contactEmployee['contactId'],
                'employeeId' => $employee['id'],
                'employeeWxId' => $employee['wxUserId'],
                'contactWxId' => $contact['wxExternalUserid'],
                'contactName' => $contactEmployee['remark'],
                'nickName' => $contact['name'],
                'corpName' => $contact['corpName'],
                'employeeName' => $employee['name'],
                'tags' => $tagName,
                'addTime' => date('Y-m-d H:i', strtotime($contactEmployee['createTime'])),
                'lastMsgTime' => $lastMsg,
                'addWay' => $wayTexts[$contactEmployee['addWay']],
            ];
        }

        //条件筛选
        $searchNum = 0;
        if ($params['contactName']) {
            ++$searchNum;
        }
        if (count($params['employeeId'])) {
            ++$searchNum;
        }
        if ($params['addTimeStart'] && $params['addTimeEnd']) {
            ++$searchNum;
        }

        $result = [];
        foreach ($res as $re) {
            $tempNum = 0;
            if ($params['contactName']) {
                if (strstr($re['nickName'], $params['contactName'])) {
                    ++$tempNum;
                }
            }

            if (count($params['employeeId'])) {
                if (in_array($re['employeeId'], $params['employeeId'])) {
                    ++$tempNum;
                }
            }

            if ($params['addTimeStart'] && $params['addTimeEnd']) {
                $startTime = strtotime($params['addTimeStart']);
                $endTime = strtotime($params['addTimeEnd']);
                $time = strtotime($re['addTime']);
                if ($time > $startTime && $time < $endTime) {
                    ++$tempNum;
                }
            }

            if ($tempNum === $searchNum) {
                $result[] = $re;
            }
        }

        return [
            'list' => $result,
            'lastTime' => $lastTime,
        ];
    }
}
