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
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 同步客户.
 *
 * Class SynContactLogic
 */
class SynContactLogic
{
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
     * 标签.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $workContactTag;

    /**
     * 分组.
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $workContactTagGroup;

    /**
     * 客户 - 标签.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $workContactTagPivot;

    /**
     * 更新头像的数据.
     * @var array
     */
    private $updateAvatar = [];

    /**
     * 添加头像的数据.
     * @var array
     */
    private $createAvatar = [];

    /**
     * 企业id.
     * @var int
     */
    private $corpId;

    /**
     * @param array $employee 用户信息
     * @param int $corpId 企业授信ID
     * @param array $wxContactIds 拉群用户跟进的客户微信ID
     * @param array $wxContactDetails 拉群用户跟进的客户微信详情信息
     */
    public function handle(array $employee, int $corpId, array $wxContactIds, array $wxContactDetails)
    {
        $this->createAvatar = [];
        $this->updateAvatar = [];
        $this->corpId       = $corpId;
        ## 查询客户列表
        $oldContactList = $this->workContact->getWorkContactByCorpIdWxExternalUserIds($corpId, array_column(array_column($wxContactDetails, 'external_contact'), 'external_userid'), ['id', 'wx_external_userid']);
        ## 查询当前公司所有的客户标签
        $oldTags = $this->workContactTag->getWorkContactTagsByCorpId([$corpId], ['id', 'wx_contact_tag_id']);
        ## 查询当前公司所有的客户标签分组
        $oldTagGroups = $this->workContactTagGroup->getWorkContactTagGroupsByCorpId([$corpId], ['id', 'group_name']);
        ## 处理客户及客户标签信息
        $handleContactRes = $this->handleContact($employee, $corpId, $wxContactDetails, $oldContactList, $oldTags, $oldTagGroups);
        if ($handleContactRes['res']) {
            ## 更新后-客户列表
            $oldContactList = empty($handleContactRes['createContactData']) ? $oldContactList : $this->workContact->getWorkContactByCorpIdWxExternalUserIds($corpId, $wxContactIds, ['id', 'wx_external_userid']);
            ## 更新后-标签列表
            $oldTags = empty($handleContactRes['createTagData']) ? $oldTags : $this->workContactTag->getWorkContactTagsByCorpId([$corpId], ['id', 'wx_contact_tag_id']);
            ## 查询用户-客户关联表
            $oldContactEmployeeList = $this->workContactEmployee->getWorkContactEmployeesByOtherIds((int) $employee['id'], array_column($oldContactList, 'id'), ['id', 'contact_id']);
            ## 查询客户-标签关联表
            $oldContactTagList = $this->workContactTagPivot->getWorkContactTagPivotsByContactIdEmployeeId(array_column($oldContactList, 'id'), (int) $employee['id'], ['id', 'contact_id', 'contact_tag_id']);
            ## 处理用户-客户信息
            $this->handleEmployeeContact($employee, $corpId, $handleContactRes, $oldContactList, $oldTags, $oldContactEmployeeList, $oldContactTagList);
            //上传头像到阿里云
            if (! empty($this->updateAvatar)) {
                oss_up_queue($this->updateAvatar);
            }
            //上传头像到阿里云
            if (! empty($this->createAvatar)) {
                oss_up_queue($this->createAvatar);
            }
        }
    }

    /**
     * @param array $employee 用户信息
     * @param int $corpId 公司授权ID
     * @param array $wxContactDetails 微信客户信息
     * @param array $oldContactList 已存在客户信息
     * @param array $oldTags 已存在标签信息
     * @param array $oldTagGroups 已存在标签分组信息
     * @return array 响应
     */
    private function handleContact(array $employee, int $corpId, array $wxContactDetails, array $oldContactList, array $oldTags, array $oldTagGroups): array
    {
        empty($oldContactList) || $oldContactList = array_column($oldContactList, 'id', 'wxExternalUserid');
        ## 处理客户数据
        $createContactData = [];
        $updateContactData = [];
        $wxTag             = [];
        $wxContactTag      = [];
        $followUserList    = [];
        foreach ($wxContactDetails as &$val) {
            ## 处理客户标签信息
            $followUser                    = array_column($val['follow_user'], null, 'userid');
            $followUser                    = $followUser[$employee['wxUserId']];
            $followUser['wxExternalUerid'] = $val['external_contact']['external_userid'];
            $followUserList[]              = $followUser;
            if (! empty($followUser['tags'])) {
                foreach ($followUser['tags'] as $tag) {
                    if ($tag['type'] == 1) {
                        $wxContactTag[] = [
                            'tagId'           => $tag['tag_id'],
                            'wxExternalUerid' => $val['external_contact']['external_userid'],
                        ];
                        isset($wxContactTag[$tag['tag_id']]) || $wxTag[$tag['tag_id']] = [
                            'groupName' => $tag['group_name'],
                            'tagName'   => $tag['tag_name'],
                        ];
                    }
                }
            }
            ## 本地头像存储路径
            $pathFileName = 'contact/avatar/' . microtime(true) * 10000 . rand(1, 1000) . '.jpg';
            $contact      = $val['external_contact'];
            $contactData  = [
                'corp_id'            => $corpId,
                'wx_external_userid' => $contact['external_userid'],
                'name'               => $contact['name'],
                'avatar'             => empty($contact['avatar']) ? '' : $pathFileName,
                'type'               => isset($contact['type']) ? $contact['type'] : 0,
                'gender'             => isset($contact['gender']) ? $contact['gender'] : 0,
                'unionid'            => isset($contact['unionid']) ? $contact['unionid'] : '',
                'position'           => isset($contact['position']) ? $contact['position'] : '',
                'corp_name'          => isset($contact['corp_name']) ? $contact['corp_name'] : '',
                'corp_full_name'     => isset($contact['corp_full_name']) ? $contact['corp_full_name'] : '',
                'external_profile'   => isset($contact['external_profile']) ? json_encode($contact['external_profile']) : json_encode([]),
                'updated_at'         => date('Y-m-d H:i:s'),
            ];
            if (isset($oldContactList[$contact['external_userid']])) {
                $contactData['id']                                 = $oldContactList[$contact['external_userid']];
                $updateContactData[]                               = $contactData;
                empty($contact['avatar']) || $this->updateAvatar[] = [$contact['avatar'], $pathFileName];
            } else {
                $contactData['created_at']                         = date('Y-m-d H:i:s');
                $createContactData[]                               = $contactData;
                empty($contact['avatar']) || $this->createAvatar[] = [$contact['avatar'], $pathFileName];
            }
        }
        ## 查询
        empty($oldTagGroups) || $oldTagGroups = array_column($oldTagGroups, 'id', 'groupName');
        empty($oldTags) || $oldTags           = array_column($oldTags, 'id', 'wxContactTagId');
        $createTagData                        = [];
        if (! empty($wxTag)) {
            foreach ($wxTag as $k => $tag) {
                if (! isset($oldTags[$k])) {
                    $createTagData[] = [
                        'wx_contact_tag_id'    => $k,
                        'corp_id'              => $corpId,
                        'name'                 => $tag['tagName'],
                        'contact_tag_group_id' => isset($oldTagGroups[$tag['tagName']]) ? $oldTagGroups[$tag['tagName']] : 0,
                        'created_at'           => date('Y-m-d H:i:s'),
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        ## 更新客户及标签信息
        $res = $this->updateContactAndTag($createContactData, $updateContactData, $createTagData);

        return [
            'wxContactTag'      => $wxContactTag,
            'followUserList'    => $followUserList,
            'createContactData' => $createContactData,
            'createTagData'     => $createTagData,
            'res'               => $res,
        ];
    }

    /**
     * 更新客户及标签信息.
     * @param array $createContactData 需创建客户
     * @param array $updateContactData 需更新客户
     * @param array $createTagData 需创建客户标签
     * @return bool 相应
     */
    private function updateContactAndTag(array $createContactData, array $updateContactData, array $createTagData): bool
    {
        //开启事务
        Db::beginTransaction();
        try {
            ## 创建客户
            if (! empty($createContactData)) {
                $createContactRes = $this->workContact->createWorkContacts($createContactData);
                if (! $createContactRes) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量插入客户数据错误，data:' . json_encode($createContactData));
                }
            }
            ## 更新客户
            if (! empty($updateContactData)) {
                $updateContactRes = $this->workContact->updateWorkContact($updateContactData);
                if (! is_int($updateContactRes)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量更新客户数据错误，data:' . json_encode($updateContactData));
                }
            }
            ## 创建客户标签
            if (! empty($createTagData)) {
                $createContactTagRes = $this->workContactTag->createWorkContactTags($createTagData);
                if (! $createContactTagRes) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量插入客户标签数据错误，data:' . json_encode($createTagData));
                }
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('同步客户错误，error: %s [%s]', $e->getMessage(), date('Y-m-d H:i:s')));
            return false;
        }
    }

    /**
     * @param array $employee 用户信息
     * @param int $corpId 企业授信ID
     * @param array $handleContactRes 上次操作入表数据
     * @param array $oldContactList 已存在客户列表
     * @param array $oldTags 已存在客户标签列表
     * @param array $oldContactEmployeeList 已存在用户-客户关联信息
     * @param array $oldContactTagList 已存在客户-标签关联信息
     */
    private function handleEmployeeContact(array $employee, int $corpId, array $handleContactRes, array $oldContactList, array $oldTags, array $oldContactEmployeeList, array $oldContactTagList)
    {
        $oldContactList         = array_column($oldContactList, 'id', 'wxExternalUserid');
        $oldContactEmployeeList = array_column($oldContactEmployeeList, 'id', 'contactId');
        ## 处理用户-客户关联信息
        $createEmployeeContactData = [];
        $updateEmployeeContactData = [];
        foreach ($handleContactRes['followUserList'] as $followUser) {
            $contactId       = $oldContactList[$followUser['wxExternalUerid']];
            $contactEmployee = [
                'remark'           => isset($followUser['remark']) ? $followUser['remark'] : '',
                'description'      => isset($followUser['description']) ? $followUser['description'] : '',
                'remark_corp_name' => isset($followUser['remark_corp_name']) ? $followUser['remark_corp_name'] : '',
                'remark_mobiles'   => isset($followUser['remark_mobiles']) ? json_encode($followUser['remark_mobiles']) : json_encode([]),
                'add_way'          => isset($followUser['add_way']) ? $followUser['add_way'] : 0,
                'oper_userid'      => isset($followUser['oper_userid']) ? $followUser['oper_userid'] : '',
                'state'            => isset($followUser['state']) ? $followUser['state'] : '',
                'updated_at'       => date('Y-m-d H:i:s'),
            ];
            if (isset($oldContactEmployeeList[$contactId])) {
                $contactEmployee['id']       = $oldContactEmployeeList[$contactId];
                $updateEmployeeContactData[] = $contactEmployee;
            } else {
                $contactEmployee['employee_id'] = $employee['id'];
                $contactEmployee['contact_id']  = $contactId;
                $contactEmployee['corp_id']     = $corpId;
                $contactEmployee['create_time'] = isset($followUser['createtime']) ? date('Y-m-d H:i:s', $followUser['createtime']) : '';
                $contactEmployee['created_at']  = date('Y-m-d H:i:s');
                $createEmployeeContactData[]    = $contactEmployee;
            }
        }
        ## 处理客户-标签关联信息
        $createContactTag = [];
        $deleteContactTag = [];
        if (! empty($handleContactRes['wxContactTag'])) {
            $oldTags        = array_column($oldTags, 'id', 'wxContactTagId');
            $contactTagList = [];
            if (! empty($oldContactTagList)) {
                foreach ($oldContactTagList as $val) {
                    $key                  = $val['contactId'] . $val['contactTagId'];
                    $contactTagList[$key] = $val['id'];
                }
            }
            foreach ($handleContactRes['wxContactTag'] as $v) {
                $contactId    = $oldContactList[$v['wxExternalUerid']];
                $contactTagId = $oldTags[$v['tagId']];
                $k            = $contactId . $contactTagId;
                if (isset($contactTagList[$k])) {
                    unset($contactTagList[$k]);
                } else {
                    $createContactTag[] = [
                        'contact_id'     => $contactId,
                        'employee_id'    => $employee['id'],
                        'contact_tag_id' => $contactTagId,
                        'type'           => 1,
                        'updated_at'     => date('Y-m-d H:i:s'),
                        'created_at'     => date('Y-m-d H:i:s'),
                    ];
                }
            }
            $deleteContactTag = empty($contactTagList) ? [] : array_values($contactTagList);
        }
        ## 入表操作
        $this->updateEmployeeContact($createEmployeeContactData, $updateEmployeeContactData, $createContactTag, $deleteContactTag);
    }

    /**
     * 用户-客户关联信息入表.
     * @param array $createEmployeeContactData 需创建用户-客户关联信息
     * @param array $updateEmployeeContactData 需更新用户-客户关联信息
     * @param array $createContactTag 需创建客户-标签关联信息
     * @param array $deleteContactTag 需删除客户-标签关联信息
     */
    private function updateEmployeeContact(array $createEmployeeContactData, array $updateEmployeeContactData, array $createContactTag, array $deleteContactTag)
    {
        //开启事务
        Db::beginTransaction();
        try {
            ## 创建用户-客户
            if (! empty($createEmployeeContactData)) {
                $createEmployeeContactRes = $this->workContactEmployee->createWorkContactEmployees($createEmployeeContactData);
                if (! $createEmployeeContactRes) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量插入用户-客户关联数据错误，data:' . json_encode($createEmployeeContactData));
                }
            }
            ## 更新用户-客户
            if (! empty($updateEmployeeContactData)) {
                $updateEmployeeContactRes = $this->workContactEmployee->updateWorkContactEmployeesCaseIds($updateEmployeeContactData);
                if (! is_int($updateEmployeeContactRes)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量更新用户-客户关联数据错误，data:' . json_encode($updateEmployeeContactData));
                }
            }
            ## 创建客户-标签
            if (! empty($createContactTag)) {
                $createContactTagRes = $this->workContactTagPivot->createWorkContactTagPivots($createContactTag);
                if (! $createContactTagRes) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量插入客户-标签关联数据错误，data:' . json_encode($createContactTag));
                }
            }
            ## 删除客户-标签
            if (! empty($deleteContactTag)) {
                $deleteContactTagRes = $this->workContactTagPivot->deleteWorkContactTagPivots($deleteContactTag);
                if (! is_int($deleteContactTagRes)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '批量删除客户-标签关联数据错误，data:' . json_encode($deleteContactTag));
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('同步客户错误，error: %s [%s]', $e->getMessage(), date('Y-m-d H:i:s')));
        }
    }
}
