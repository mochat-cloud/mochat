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

use App\Constants\BusinessLog\Event;
use App\Constants\ChannelCode\AutoAddFriend;
use App\Constants\ChannelCode\Type;
use App\Contract\BusinessLogServiceInterface;
use App\Contract\ChannelCodeGroupServiceInterface;
use App\Contract\ChannelCodeServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 渠道码列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
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
     * 客户标签表.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $workContactTag;

    /**
     * 客户-员工关联表.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $workContactEmployee;

    /**
     * 渠道码日志表.
     * @Inject
     * @var BusinessLogServiceInterface
     */
    private $businessLog;

    /**
     * 参数.
     * @var array
     */
    private $params;

    /**
     * @return array
     */
    public function handle(array $params)
    {
        $this->params = $params;

        return $this->getInfo();
    }

    /**
     * 获取渠道码列表.
     * @return array
     */
    private function getInfo()
    {
        //处理查询信息
        $handleParams = $this->handleInfo();
        if (empty($handleParams)) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list' => [],
            ];
        }

        //查询列表
        $info = $this->channelCode->getChannelCodeList($handleParams['where'], $handleParams['columns'], $handleParams['options']);

        if (empty($info['data'])) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list' => [],
            ];
        }

        $groupIds = array_unique(array_column($info['data'], 'groupId'));
        //根据分组id查询分组名
        $groupInfo = $this->getGroup($groupIds);

        $ids = array_column($info['data'], 'id');
        //根据id查询客户数
        $contact = $this->getContact($ids);

        //拼接数据
        foreach ($info['data'] as &$val) {
            $val['channelCodeId'] = $val['id'];
            $val['groupName']     = '';
            if (isset($groupInfo[$val['groupId']])) {
                $val['groupName'] = $groupInfo[$val['groupId']]['name'];
            }

            if (! empty($val['tags'])) {
                $tagIds      = json_decode($val['tags'], true);
                $val['tags'] = $this->getTagInfo($tagIds);
            }

            $val['contactNum'] = 0;
            if (isset($contact['channelCode-' . $val['id']])) {
                $val['contactNum'] = count($contact['channelCode-' . $val['id']]['contact']);
            }

            if ($val['autoAddFriend'] != 0) {
                $val['autoAddFriend'] = AutoAddFriend::getMessage($val['autoAddFriend']);
            }

            $val['qrcodeUrl'] = empty($val['qrcodeUrl']) ? '' : file_full_url($val['qrcodeUrl']);
            $val['type']      = Type::getMessage($val['type']);

            $val['user'] = user();
        }

        return [
            'page' => [
                'perPage'   => isset($info['per_page']) ? $info['per_page'] : 20,
                'total'     => isset($info['total']) ? $info['total'] : 0,
                'totalPage' => isset($info['last_page']) ? $info['last_page'] : 0,
            ],
            'list' => $info['data'],
        ];
    }

    /**
     * 获取客户数.
     * @param $ids
     * @return array
     */
    private function getContact($ids)
    {
        $channelCodeIds = [];
        foreach ($ids as $val) {
            $channelCodeIds[] = 'channelCode-' . $val;
        }

        //查询客户数
        $contactEmployee = $this->workContactEmployee->getWorkContactEmployeesByStates($channelCodeIds, ['contact_id', 'state']);
        if (empty($contactEmployee)) {
            return [];
        }

        $contact = [];
        foreach ($contactEmployee as &$val) {
            if (isset($contact[$val['state']])) {
                $contact[$val['state']]['contact'][] = $val['contactId'];
            } else {
                $tmp              = [];
                $tmp['contact'][] = $val['contactId'];

                $contact[$val['state']] = $tmp;
            }
        }

        return $contact;
    }

    /**
     * 获取标签信息.
     * @param $tagIds
     * @return array
     */
    private function getTagInfo($tagIds)
    {
        $res = $this->workContactTag->getWorkContactTagsById($tagIds);
        if (empty($res)) {
            return [];
        }

        return array_column($res, 'name');
    }

    /**
     * 查询分组信息.
     * @param $groupIds
     * @return array
     */
    private function getGroup($groupIds)
    {
        $res = $this->channelCodeGroup->getChannelCodeGroupsById($groupIds);
        if (empty($res)) {
            return [];
        }

        return array_column($res, null, 'id');
    }

    /**
     * 处理查询信息.
     * @return array
     */
    private function handleInfo()
    {
        //处理查询条件
        $where = $this->handleWhere();

        //数据权限 0-全企业，1-本部门数据，2-当前登录人
        if (user()['dataPermission'] != 0) {
            //员工id
            $operationId = array_unique(user()['deptEmployeeIds']);
            //查询这些员工创建的渠道码（渠道码列表以创建者为主，只能看见自己创建或者本部门创建的渠道码）
            $businessLog = $this->businessLog->getBusinessLogsByOperationIdEvent(
                $operationId,
                Event::CHANNEL_CODE_CREATE,
                [
                    'business_id',
                ]
            );
            if (empty($businessLog)) {
                return [];
            }

            $where['id'] = array_column($businessLog, 'businessId');
        }

        $columns = [
            'id',
            'group_id',
            'name',
            'qrcode_url',
            'auto_add_friend',
            'tags',
            'type',
        ];
        $options = [
            'orderByRaw' => 'updated_at desc',
            'perPage'    => isset($this->params['perPage']) ? (int) $this->params['perPage'] : 20,
            'page'       => isset($this->params['page']) ? (int) $this->params['page'] : 1,
            'pageName'   => 'page',
        ];

        return [
            'where'   => $where,
            'columns' => $columns,
            'options' => $options,
        ];
    }

    /**
     * 处理查询条件.
     * @return array
     */
    private function handleWhere()
    {
        $where = [];
        //企业id
        $where['corp_id'] = user()['corpIds'];

        //活码名称搜索
        if (! empty($this->params['name'])) {
            $where[] = ['name', 'like', '%' . $this->params['name'] . '%'];
        }
        //活码类型搜索
        if (! empty($this->params['type'])) {
            $where['type'] = $this->params['type'];
        }
        //分组搜索
        if (is_numeric($this->params['groupId'])) {
            $where['group_id'] = $this->params['groupId'];
        }

        return $where;
    }
}
