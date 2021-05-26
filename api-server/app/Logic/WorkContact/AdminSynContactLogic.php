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

use App\Constants\WorkContactEmployee\Status;
use App\Constants\WorkUpdateTime\Type;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 管理员同步客户.
 *
 * Class AdminSynContactLogic
 */
class AdminSynContactLogic
{
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
     * 同步时间表.
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

    /**
     * 更新客户表参数.
     * @var array
     */
    private $updateContact = [];

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
     * @param $params
     */
    public function handle($params)
    {
        //所有成员id
        $employeeIds = array_column($params['employee'], 'id');

        //查询成员下所有客户信息
        $contactInfo = $this->getContactEmployee($employeeIds);

        //同步客户
        $this->handleContact($params, $contactInfo);
        //更新客户同步时间
        $this->updateSynTime();
    }

    /**
     * 同步客户.
     * @param $params
     * @param $contactInfo
     */
    private function handleContact($params, $contactInfo)
    {
        $employee = array_column($params['employee'], null, 'wxUserId');

        //组织同步客户数据
        foreach ($params['detail'] as $val) {
            //若本地有该客户 更新比对客户相关信息
            if (isset($contactInfo[$val['external_contact']['external_userid']])) {
                $this->updateContactInfo($contactInfo, $val, $employee);
            } else { //若查询不到客户信息 添加客户相关信息
                $this->addContact($val, $employee, $params['corpId']);
            }
        }
        //更新客户相关信息（客户表）
        if (! empty($this->updateContact)) {
            $updateRes = $this->workContact->updateWorkContact($this->updateContact);
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '修改客户信息失败');
            }
        }

        //上传头像到阿里云
        if (! empty($this->updateAvatar)) {
            oss_up_queue($this->updateAvatar);
        }
        //上传头像到阿里云
        if (! empty($this->createAvatar)) {
            oss_up_queue($this->createAvatar);
        }
    }

    /**
     * 本地没有该客户 添加客户相关信息.
     * @param $val
     * @param $employee
     * @param $corpId
     */
    private function addContact($val, $employee, $corpId)
    {
        $addContactEmployee = [];
        $addContactTag      = [];

        //开启事务
        Db::beginTransaction();

        try {
            //本地头像存储路径
            $pathFileName = 'contact/avatar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg';

            //添加客户表
            $addContact = [
                'corp_id'            => $corpId,
                'wx_external_userid' => $val['external_contact']['external_userid'],
                'name'               => $val['external_contact']['name'],
                'avatar'             => empty($val['external_contact']['avatar']) ? '' : $pathFileName,
                'type'               => isset($val['external_contact']['type']) ? $val['external_contact']['type'] : 0,
                'gender'             => isset($val['external_contact']['gender']) ? $val['external_contact']['gender'] : 0,
                'unionid'            => isset($val['external_contact']['unionid']) ? $val['external_contact']['unionid'] : '',
                'position'           => isset($val['external_contact']['position']) ? $val['external_contact']['position'] : '',
                'corp_name'          => isset($val['external_contact']['corp_name']) ? $val['external_contact']['corp_name'] : '',
                'corp_full_name'     => isset($val['external_contact']['corp_full_name']) ? $val['external_contact']['corp_full_name'] : '',
                'external_profile'   => isset($val['external_contact']['external_profile']) ? json_encode($val['external_contact']['external_profile']) : json_encode([]),
            ];
            $addContactRes = $this->workContact->createWorkContact($addContact);
            if (! is_int($addContactRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户失败');
            }

            //组织头像数据
            if (! empty($val['external_contact']['avatar'])) {
                $this->createAvatar[] = [
                    $val['external_contact']['avatar'],
                    $pathFileName,
                ];
            }

            //客户与员工关联
            foreach ($val['follow_user'] as $item) {
                $employeeId = 0;
                if (isset($employee[$item['userid']])) {
                    $employeeId = $employee[$item['userid']]['id'];
                }

                $addContactEmployee[] = [
                    'employee_id'      => $employeeId,
                    'contact_id'       => $addContactRes,
                    'remark'           => isset($item['remark']) ? $item['remark'] : '',
                    'description'      => isset($item['description']) ? $item['description'] : '',
                    'remark_corp_name' => isset($item['remark_corp_name']) ? $item['remark_corp_name'] : '',
                    'remark_mobiles'   => isset($item['remark_mobiles']) ? json_encode($item['remark_mobiles']) : json_encode([]),
                    'add_way'          => isset($item['add_way']) ? $item['add_way'] : 0,
                    'oper_userid'      => isset($item['oper_userid']) ? $item['oper_userid'] : '',
                    'state'            => isset($item['state']) ? $item['state'] : '',
                    'corp_id'          => $corpId,
                    'create_time'      => isset($item['createtime']) ? date('Y-m-d H:i:s', $item['createtime']) : '',
                ];

                //客户与标签
                if (! empty($item['tags'])) {
                    foreach ($item['tags'] as $v) {
                        $tagId = 0;
                        //根据分组名查询分组id
                        $groupInfo = $this->workContactTagGroup->getWorkContactTagGroupByCorpIdName($corpId, $v['group_name'], ['id']);
                        if (! empty($groupInfo)) {
                            $groupId = $groupInfo['id'];
                            if ($groupId != 0) {
                                //根据分组id和标签名称查询标签id
                                $tagInfo = $this->workContactTag->getWorkContactTagByGroupIdName($groupId, $v['tag_name']);
                                if (! empty($tagInfo)) {
                                    $tagId = $tagInfo['id'];
                                }
                            }

                            $addContactTag[] = [
                                'contact_id'     => $addContactRes,
                                'employee_id'    => $employeeId,
                                'contact_tag_id' => $tagId,
                                'type'           => $v['type'],
                            ];
                        }
                    }

                    if (! empty($addContactTag)) {
                        //添加客户、标签关联表
                        $contactTagPivotRes = $this->workContactTagPivot->createWorkContactTagPivots($addContactTag);
                        if ($contactTagPivotRes != true) {
                            throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
                        }
                    }
                }
            }

            if (! empty($addContactEmployee)) {
                //添加员工、客户关联表
                $createRes = $this->workContactEmployee->createWorkContactEmployees($addContactEmployee);
                if ($createRes != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加员工客户关系失败');
                }
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * 设置了客户 更新比对客户相关信息.
     * @param $contactInfo
     * @param $val
     * @param $employee
     */
    private function updateContactInfo($contactInfo, $val, $employee)
    {
        //本地头像存储路径
        $pathFileName = 'contact/avatar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg';

        $this->updateContact[] = [
            'id'               => $contactInfo[$val['external_contact']['external_userid']]['id'],
            'name'             => $val['external_contact']['name'],
            'avatar'           => empty($val['external_contact']['avatar']) ? '' : $pathFileName,
            'type'             => isset($val['external_contact']['type']) ? $val['external_contact']['type'] : 0,
            'gender'           => isset($val['external_contact']['gender']) ? $val['external_contact']['gender'] : 0,
            'unionid'          => isset($val['external_contact']['unionid']) ? $val['external_contact']['unionid'] : '',
            'position'         => isset($val['external_contact']['position']) ? $val['external_contact']['position'] : '',
            'corp_name'        => isset($val['external_contact']['corp_name']) ? $val['external_contact']['corp_name'] : '',
            'corp_full_name'   => isset($val['external_contact']['corp_full_name']) ? $val['external_contact']['corp_full_name'] : '',
            'external_profile' => isset($val['external_contact']['external_profile']) ? json_encode($val['external_contact']['external_profile']) : json_encode([]),
        ];

        //组织头像数据
        if (! empty($val['external_contact']['avatar'])) {
            $this->updateAvatar[] = [
                $val['external_contact']['avatar'],
                $pathFileName,
            ];
        }

        $employeeIds = [];
        //客户与员工关联
        if (! empty($val['follow_user'])) {
            $addContactEmployee = [];
            $addContactTag      = [];

            //客户与员工关联
            foreach ($val['follow_user'] as $item) {
                $employeeId = 0;

                if (isset($employee[$item['userid']])) {
                    $employeeId    = $employee[$item['userid']]['id'];
                    $employeeIds[] = $employee[$item['userid']]['id'];
                }

                //更新员工与客户关系
                if (in_array($employeeId, $contactInfo[$val['external_contact']['external_userid']]['employeeIds'])) {
                    $updateContactEmployee = [
                        'remark'           => isset($item['remark']) ? $item['remark'] : '',
                        'description'      => isset($item['description']) ? $item['description'] : '',
                        'remark_corp_name' => isset($item['remark_corp_name']) ? $item['remark_corp_name'] : '',
                        'remark_mobiles'   => isset($item['remark_mobiles']) ? json_encode($item['remark_mobiles']) : json_encode([]),
                        'state'            => isset($item['state']) ? $item['state'] : '',
                        'create_time'      => isset($item['createtime']) ? date('Y-m-d H:i:s', $item['createtime']) : '',
                    ];
                    //更新员工、客户关联表
                    $updateRes = $this->workContactEmployee->updateWorkContactEmployeeByOtherIds(
                        $employeeId,
                        $contactInfo[$val['external_contact']['external_userid']]['id'],
                        $updateContactEmployee
                    );

                    if (! is_int($updateRes)) {
                        throw new CommonException(ErrorCode::SERVER_ERROR, '修改员工客户关联信息失败');
                    }
                } else {
                    //新增员工与客户关系
                    $addContactEmployee[] = [
                        'employee_id'      => $employeeId,
                        'contact_id'       => $contactInfo[$val['external_contact']['external_userid']]['id'],
                        'remark'           => isset($item['remark']) ? $item['remark'] : '',
                        'description'      => isset($item['description']) ? $item['description'] : '',
                        'remark_corp_name' => isset($item['remark_corp_name']) ? $item['remark_corp_name'] : '',
                        'remark_mobiles'   => isset($item['remark_mobiles']) ? json_encode($item['remark_mobiles']) : json_encode([]),
                        'add_way'          => isset($item['add_way']) ? $item['add_way'] : 0,
                        'oper_userid'      => isset($item['oper_userid']) ? $item['oper_userid'] : '',
                        'state'            => isset($item['state']) ? $item['state'] : '',
                        'corp_id'          => user()['corpIds'][0],
                        'create_time'      => isset($item['createtime']) ? date('Y-m-d H:i:s', $item['createtime']) : '',
                    ];
                }

                //查询员工对客户所打的标签
                $contactTag = $this->workContactTagPivot->getWorkContactTagPivotsByOtherId(
                    $contactInfo[$val['external_contact']['external_userid']]['id'],
                    $employeeId,
                    [
                        'id',
                        'contact_tag_id',
                    ]
                );
                //员工现有标签
                $tagIds = [];
                if (! empty($contactTag)) {
                    $tagIds = array_column($contactTag, 'contactTagId');
                }

                $wxTagIds = [];
                //客户与标签
                if (! empty($item['tags'])) {
                    foreach ($item['tags'] as $v) {
                        $wxTagId = 0;
                        //根据分组名查询分组id
                        $groupInfo = $this->workContactTagGroup->getWorkContactTagGroupByCorpIdName(user()['corpIds'][0], $v['group_name'], ['id']);
                        if (! empty($groupInfo)) {
                            $groupId = $groupInfo['id'];
                            if ($groupId != 0) {
                                //根据分组id和标签名称查询标签id
                                $tagInfo = $this->workContactTag->getWorkContactTagByGroupIdName($groupId, $v['tag_name']);
                                if (! empty($tagInfo)) {
                                    $wxTagId = $tagInfo['id'];
                                    //微信获取到的标签
                                    $wxTagIds[] = $tagInfo['id'];
                                }
                            }
                        }

                        //如果微信获取到的标签id不在客户现有的标签数组内 则添加客户标签
                        if ($wxTagId != 0 && ! in_array($wxTagId, $tagIds)) {
                            //添加客户标签
                            $addContactTag[] = [
                                'contact_id'     => $contactInfo[$val['external_contact']['external_userid']]['id'],
                                'employee_id'    => $employeeId,
                                'contact_tag_id' => $wxTagId,
                                'type'           => $v['type'],
                            ];
                        }
                    }

                    //添加客户、标签关联表
                    if (! empty($addContactTag)) {
                        //添加客户、标签关联表
                        $contactTagPivotRes = $this->workContactTagPivot->createWorkContactTagPivots($addContactTag);
                        if ($contactTagPivotRes != true) {
                            throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
                        }
                    }
                }

                //本地客户标签与微信获取的客户标签的差集 则是需要删除的本地客户标签
                $deleteContactTagIds = array_diff($tagIds, $wxTagIds);
                if (! empty($deleteContactTagIds)) {
                    $deleteContactTagRes = $this->workContactTagPivot->deleteWorkContactTagPivotsByOtherId(
                        $contactInfo[$val['external_contact']['external_userid']]['id'],
                        $employeeId,
                        $deleteContactTagIds
                    );

                    if (! is_int($deleteContactTagRes)) {
                        throw new CommonException(ErrorCode::SERVER_ERROR, '删除客户标签失败');
                    }
                }
            }

            //添加员工、客户关联表
            if (! empty($addContactEmployee)) {
                //添加员工、客户关联表
                $createRes = $this->workContactEmployee->createWorkContactEmployees($addContactEmployee);
                if ($createRes != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加员工客户关系失败');
                }
            }
        }

        //取本地客户所属的员工id与企业微信获取的所属员工id的差集  删除本地员工客户对应关系
        $deleteEmployeeIds = array_diff($contactInfo[$val['external_contact']['external_userid']]['employeeIds'], $employeeIds);
        if (! empty($deleteEmployeeIds)) {
            //更新status为删除
            $updateRes = $this->workContactEmployee->updateWorkContactEmployeesByOtherIds(
                $deleteEmployeeIds,
                $contactInfo[$val['external_contact']['external_userid']]['id'],
                ['status' => Status::REMOVE]
            );
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '修改删除状态失败');
            }
            $deleteRes = $this->workContactEmployee
                ->deleteWorkContactEmployeesByOtherIds($contactInfo[$val['external_contact']['external_userid']]['id'], $deleteEmployeeIds);

            if (! is_int($deleteRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '删除客户与员工关系失败');
            }
        }
    }

    /**
     * 获取客户信息.
     * @param $contactEmployee
     * @return array
     */
    private function getContactInfo($contactEmployee)
    {
        $contactIds = array_unique(array_column($contactEmployee, 'contactId'));

        $contactInfo = $this->workContact->getWorkContactsById($contactIds, ['id', 'wx_external_userid']);
        if (! empty($contactInfo)) {
            $contactInfo = array_column($contactInfo, null, 'wxExternalUserid');

            foreach ($contactInfo as &$val) {
                $val['employeeIds'] = [];
                if (isset($contactEmployee[$val['id']])) {
                    $val['employeeIds'] = $contactEmployee[$val['id']]['employeeId'];
                }
            }
            unset($val);
        }

        return $contactInfo;
    }

    /**
     * 获取客户成员关系.
     * @param $employeeIds
     * @return array
     */
    private function getContactEmployee($employeeIds)
    {
        $contactEmployee = $this->workContactEmployee
            ->getWorkContactEmployeesByEmployeeIds($employeeIds, ['contact_id', 'employee_id']);

        if (empty($contactEmployee)) {
            return [];
        }

        $contactEmployeeData = [];
        foreach ($contactEmployee as &$raw) {
            if (isset($contactEmployeeData[$raw['contactId']])) {
                $contactEmployeeData[$raw['contactId']]['contactId']    = $raw['contactId'];
                $contactEmployeeData[$raw['contactId']]['employeeId'][] = $raw['employeeId'];
            } else {
                $tmp                 = [];
                $tmp['contactId']    = $raw['contactId'];
                $tmp['employeeId'][] = $raw['employeeId'];

                $contactEmployeeData[$raw['contactId']] = $tmp;
            }
        }
        unset($raw);

        $contactInfo = [];
        if (! empty($contactEmployeeData)) {
            //查询成员下所有客户信息
            $contactInfo = $this->getContactInfo($contactEmployeeData);
        }

        return $contactInfo;
    }

    /**
     * 更新客户同步时间.
     */
    private function updateSynTime()
    {
        //查询当前企业有没有同步客户的时间
        $workUpdateTime = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType(user()['corpIds'], (int) Type::CONTACT);
        //如果查到 就更新
        if (! empty($workUpdateTime)) {
            $data['last_update_time'] = date('Y-m-d H:i:s');
            $id                       = end($workUpdateTime)['id'];
            $updateRes                = $this->workUpdateTime->updateWorkUpdateTimeById((int) $id, $data);
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '更新客户同步时间失败');
            }
        } else {
            //如果没有新增
            $params = [
                'corp_id'          => user()['corpIds'][0],
                'type'             => Type::CONTACT,
                'last_update_time' => date('Y-m-d H:i:s'),
            ];

            $createRes = $this->workUpdateTime->createWorkUpdateTime($params);
            if (! is_int($createRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户同步时间失败');
            }
        }
    }
}
