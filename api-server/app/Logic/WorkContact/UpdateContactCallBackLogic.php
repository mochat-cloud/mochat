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
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 编辑企业客户回调.
 *
 * Class UpdateContactCallBackLogic
 */
class UpdateContactCallBackLogic
{
    /**
     * 员工 - 客户关联.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $contactEmployee;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTag;

    /**
     * 客户 - 标签关联.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivot;

    /**
     * 分组.
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $workContactTagGroup;

    public function handle(array $params)
    {
        $data = [];

        //编辑员工客户关联信息
        foreach ($params['followUser'] as $val) {
            //获取当前成员对客户的修改信息
            if ($params['wxUserId'] == $val['userid']) {
                $data = $val;
            }
        }

        if (! empty($data)) {
            $contactEmployeeData = [
                'remark'         => $data['remark'],
                'description'    => $data['description'],
                'add_way'        => $data['add_way'],
                'remark_mobiles' => ! empty($data['remark_mobiles']) ? json_encode($data['remark_mobiles']) : json_encode([]),
            ];

            //修改员工客户关系
            $updateContactEmployee = $this->contactEmployee->updateWorkContactEmployeeByOtherIds(
                $params['employeeId'],
                $params['contactId'],
                $contactEmployeeData
            );
            if (! is_int($updateContactEmployee)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '编辑客户信息回调失败');
            }

            //修改客户标签
            $this->handleContactTag($params, $data['tags']);
        }
    }

    /**
     * 修改客户标签.
     * @param $params
     * @param $tags
     */
    private function handleContactTag($params, $tags)
    {
        //查询员工对客户打的标签
        $contactTag = $this->contactTagPivot->getWorkContactTagPivotsByOtherId(
            $params['contactId'],
            $params['employeeId'],
            [
                'id',
                'contact_tag_id',
            ]
        );
        //现有表中标签 与微信详情里的标签比对
        $contactTagIds = [];
        if (! empty($contactTag)) {
            //现有标签id
            $contactTagIds = array_column($contactTag, 'contactTagId');
        }

        $wxTagIds = [];
        if (! empty($tags)) {
            $addContactTag = [];
            $wxTagId       = 0;
            foreach ($tags as $v) {
                //根据分组名查询分组id
                $groupInfo = $this->workContactTagGroup->getWorkContactTagGroupByCorpIdName($params['corpId'], $v['group_name'], ['id']);
                if (! empty($groupInfo)) {
                    $groupId = $groupInfo['id'];
                    if ($groupId != 0) {
                        //根据分组id和标签名称查询标签id
                        $tagInfo = $this->contactTag->getWorkContactTagByGroupIdName($groupId, $v['tag_name']);
                        if (! empty($tagInfo)) {
                            if (! empty($tagInfo)) {
                                $wxTagId = $tagInfo['id'];
                                //微信获取到的标签
                                $wxTagIds[] = $tagInfo['id'];
                            }
                        }
                    }
                }

                //如果微信获取到的标签id不在客户现有的标签数组内 则添加客户标签
                if ($wxTagId != 0 && ! in_array($wxTagId, $contactTagIds)) {
                    //添加客户标签
                    $addContactTag[] = [
                        'contact_id'     => $params['contactId'],
                        'employee_id'    => $params['employeeId'],
                        'contact_tag_id' => $wxTagId,
                        'type'           => $v['type'],
                    ];
                }
            }
            //添加客户标签
            if (! empty($addContactTag)) {
                $contactTagPivotRes = $this->contactTagPivot->createWorkContactTagPivots($addContactTag);
                if ($contactTagPivotRes != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
                }
            }
        }

        //本地客户标签与微信获取的客户标签的差集 则是需要删除的本地客户标签
        $deleteContactTagIds = array_diff($contactTagIds, $wxTagIds);
        if (! empty($deleteContactTagIds)) {
            $deleteContactTagRes = $this->contactTagPivot->deleteWorkContactTagPivotsByOtherId(
                $params['contactId'],
                $params['employeeId'],
                $deleteContactTagIds
            );

            if (! is_int($deleteContactTagRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除客户标签失败');
            }
        }
    }
}
