<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode;

use App\Constants\ChannelCode\Week;
use App\Constants\ChannelCode\WelcomeType;
use App\Contract\ChannelCodeGroupServiceInterface;
use App\Contract\ChannelCodeServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkDepartmentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 渠道码列表.
 *
 * Class ShowLogic
 */
class ShowLogic
{
    /**
     * 标签分组表.
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    protected $workContactTagGroupService;

    /**
     * 标签表.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    protected $workContactTagService;

    /**
     * 渠道码表.
     * @Inject
     * @var ChannelCodeServiceInterface
     */
    private $channelCode;

    /**
     * 渠道码分组表.
     * @Inject
     * @var ChannelCodeGroupServiceInterface
     */
    private $channelCodeGroup;

    /**
     * 员工表.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $workEmployee;

    /**
     * 部门表.
     * @Inject
     * @var WorkDepartmentServiceInterface
     */
    private $workDepartment;

    /**
     * 素材库表.
     * @Inject
     * @var MediumServiceInterface
     */
    private $medium;

    /**
     * 参数.
     * @var array
     */
    private $params;

    public function handle(array $params)
    {
        $this->params = $params;

        return $this->getInfo();
    }

    /**
     * 返回信息.
     * @return array
     */
    private function getInfo()
    {
        $info = $this->channelCode->getChannelCodeById((int) $this->params['channelCodeId']);
        if (empty($info)) {
            return [];
        }
        $workContactTagList = $this->getWorkContactTagList(json_decode($info['tags'], true), user()['corpIds'][0]);
        //基础信息
        $baseInfo = [
            'groupId'       => $info['groupId'],
            'groupName'     => ! empty($this->getGroup($info['groupId'])) ? $this->getGroup($info['groupId'])['name'] : '未分组',
            'name'          => $info['name'],
            'autoAddFriend' => $info['autoAddFriend'],
            'tags'          => isset($workContactTagList['tagGroupAllList']) ? $workContactTagList['tagGroupAllList'] : [],
            'selectedTags'  => isset($workContactTagList['selectedTags']) ? $workContactTagList['selectedTags'] : [],
        ];

        return [
            'baseInfo'         => $baseInfo,
            'drainageEmployee' => $this->handleDrainage($info),
            'welcomeMessage'   => $this->handleWelcome($info),
        ];
    }

    /**
     * 处理欢迎语设置.
     * @param $info
     * @return mixed
     */
    private function handleWelcome($info)
    {
        $welcomeMessage = json_decode($info['welcomeMessage'], true);

        if (empty($welcomeMessage['messageDetail'])) {
            return $welcomeMessage;
        }

        foreach ($welcomeMessage['messageDetail'] as &$value) {
            //通用欢迎语
            if ($value['type'] == WelcomeType::GENERAL) {
                //获取素材库信息
                $data             = $this->medium->getMediumById((int) $value['mediumId'], ['type', 'content']);
                $value['content'] = [];
                if (! empty($data)) {
                    $value['content'] = $this->medium->addFullPath(json_decode($data['content'], true), $data['type']);
                }
            } else {
                //周期、特殊欢迎语
                foreach ($value['detail'] as &$val) {
                    foreach ($val['timeSlot'] as &$v) {
                        //获取素材库信息
                        $data         = $this->medium->getMediumById((int) $v['mediumId'], ['type', 'content']);
                        $v['content'] = [];
                        if (! empty($data)) {
                            $v['content'] = $this->medium->addFullPath(json_decode($data['content'], true), $data['type']);
                        }
                    }
                    unset($v);
                }
                unset($val);
            }
        }
        unset($value);

        return $welcomeMessage;
    }

    /**
     * 处理引流成员设置.
     * @param $info
     * @return mixed
     */
    private function handleDrainage($info)
    {
        $drainageEmployee = json_decode($info['drainageEmployee'], true);
        //处理企业成员
        if (! empty($drainageEmployee['employees'])) {
            foreach ($drainageEmployee['employees'] as &$val) {
                $val['weekText'] = Week::getMessage($val['week']);

                foreach ($val['timeSlot'] as &$v) {
                    //根据成员id查询成员名称
                    $v['selectMembers'] = $this->getEmployee($v['employeeId']);
                    //根据部门id查询部门名称
                    if (! empty($v['departmentId'])) {
                        $v['departmentName'] = $this->getDepartment($v['departmentId']);
                    }
                }
                unset($v);
            }
            unset($val);
        }

        //处理特殊时期
        if (! empty($drainageEmployee['specialPeriod']['detail'])) {
            foreach ($drainageEmployee['specialPeriod']['detail'] as &$value) {
                foreach ($value['timeSlot'] as &$v) {
                    //根据成员id查询成员名称
                    $employeeName = $this->getEmployee($v['employeeId']);
                    //$v['employeeName'] = implode(',', $employeeName);
                    $v['selectMembers'] = $employeeName;
                }
                unset($v);
            }
            unset($value);
        }

        //处理员工添加上限
        if (! empty($drainageEmployee['addMax'])) {
            $drainageEmployee['addMax']['spareEmployeeName'] = $this->getEmployee($drainageEmployee['addMax']['spareEmployeeIds']);

            if (! empty($drainageEmployee['addMax']['employees'])) {
                foreach ($drainageEmployee['addMax']['employees'] as &$val) {
                    $val['employeeName'] = ! empty($this->getEmployee([$val['employeeId']])) ? $this->getEmployee([$val['employeeId']])[0] : '';
                }
                unset($val);
            }
        }

        return $drainageEmployee;
    }

    /**
     * 获取成员名称.
     * @param $employeeIds
     * @return array
     */
    private function getEmployee($employeeIds)
    {
        if (! is_array($employeeIds)) {
            $employeeIds = explode(',', $employeeIds);
        }
        $res = $this->workEmployee->getWorkEmployeesById($employeeIds, ['name']);
        if (empty($res)) {
            return [];
        }

        return array_column($res, 'name');
    }

    /**
     * 获取部门名称.
     * @param $departmentIds
     * @return array
     */
    private function getDepartment($departmentIds)
    {
        $res = $this->workDepartment->getWorkDepartmentsById($departmentIds, ['name']);
        if (empty($res)) {
            return [];
        }

        return array_column($res, 'name');
    }

    /**
     * 获取渠道码分组信息.
     * @param $groupId
     * @return array
     */
    private function getGroup($groupId)
    {
        $res = $this->channelCodeGroup->getChannelCodeGroupById((int) $groupId, ['name']);
        if (empty($res)) {
            return [];
        }

        return $res;
    }

    /**
     * @param array $workContactTagIds 客户标签ID数组
     * @param int $corpId 企业授信ID
     * @return array 响应数组
     */
    private function getWorkContactTagList(array $workContactTagIds, int $corpId): array
    {
        ## 当前企业全部客户标签
        $tagList = $this->workContactTagService->getWorkContactTagsByCorpId([$corpId], ['id', 'name', 'contact_tag_group_id']);
        if (empty($tagList)) {
            return [];
        }
        ## 客户标签分组
        $contactTagGroupIds = array_filter(array_unique(array_column($tagList, 'contactTagGroupId')));
        $tagGroupList       = $this->workContactTagGroupService->getWorkContactTagGroupsById($contactTagGroupIds, ['id', 'group_name']);
        $tagGroupAllList    = [
            '0' => [
                'groupId'   => 0,
                'groupName' => '未分组',
                'list'      => [],
            ],
        ];
        if (! empty($tagGroupList)) {
            foreach ($tagGroupList as $tagGroup) {
                $tagGroupAllList[$tagGroup['id']] = [
                    'groupId'   => $tagGroup['id'],
                    'groupName' => $tagGroup['groupName'],
                    'list'      => [],
                ];
            }
        }
        ## 标签分类处理
        $selectedTags = [];
        foreach ($tagList as $tag) {
            if (! isset($tagGroupAllList[$tag['contactTagGroupId']])) {
                continue;
            }
            in_array($tag['id'], $workContactTagIds) && $selectedTags[] = $tag['id'];
            $tagGroupAllList[$tag['contactTagGroupId']]['list'][]       = [
                'tagId'      => $tag['id'],
                'tagName'    => $tag['name'],
                'isSelected' => in_array($tag['id'], $workContactTagIds) ? 1 : 2,
            ];
        }
        return [
            'tagGroupAllList' => array_values($tagGroupAllList),
            'selectedTags'    => $selectedTags,
        ];
    }
}
