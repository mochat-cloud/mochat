<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContactTag;

use App\Constants\WorkUpdateTime\Type;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 同步企业客户标签.
 *
 * Class SynContactTagLogic
 */
class SynContactTagLogic
{
    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

    /**
     * @param $params
     */
    public function handle($params)
    {
        $allTag = [];
        //查询表中所有分组
        $allGroup = $this->contactTagGroupService
            ->getWorkContactTagGroupsByCorpId(user()['corpIds']);
        if (! empty($allGroup)) {
            $allGroup = array_column($allGroup, null, 'wxGroupId');
            $groupIds = array_column($allGroup, 'id');

            //查询表中所有标签
            $allTag = $this->contactTagService
                ->getWorkContactTagsByCorpIdGroupIds(user()['corpIds'], $groupIds);
            if (! empty($allTag)) {
                $allTag = array_column($allTag, null, 'wxContactTagId');
            }
        }

        //处理同步数据
        $this->synTag($params, $allGroup, $allTag);

        //更新标签同步时间
        $this->updateSynTime();
    }

    /**
     * 处理同步数据.
     * @param $params
     * @param $allGroup
     * @param $allTag
     */
    private function synTag($params, $allGroup, $allTag)
    {
        $updateTagGroup = [];
        $createTag      = [];
        $updateTag      = [];

        foreach ($params['tag_group'] as &$val) {
            //若表中有值 需更新
            if (isset($allGroup[$val['group_id']])) {
                $updateTagGroup[] = [
                    'id'          => $allGroup[$val['group_id']]['id'],
                    'wx_group_id' => $val['group_id'],
                    'group_name'  => $val['group_name'],
                    'order'       => $val['order'],
                ];

                $contactGroupId = $allGroup[$val['group_id']]['id'];

                //最后没有被unset掉的 则是企业微信没有的 即需要删除的分组
                unset($allGroup[$val['group_id']]);
            } else { //若表中没值 需新增
                $params = [
                    'wx_group_id' => $val['group_id'],
                    'corp_id'     => user()['corpIds'][0],
                    'group_name'  => $val['group_name'],
                    'order'       => $val['order'],
                ];
                $contactGroupId = $this->contactTagGroupService->createWorkContactTagGroup($params);
                if (! is_int($contactGroupId)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '标签分组创建失败');
                }
            }
            //同步标签
            foreach ($val['tag'] as &$v) {
                //若表中有值 需更新
                if (isset($allTag[$v['id']])) {
                    $updateTag[] = [
                        'id'                => $allTag[$v['id']]['id'],
                        'wx_contact_tag_id' => $v['id'],
                        'name'              => $v['name'],
                        'order'             => $v['order'],
                    ];
                    //最后没有被unset掉的 则是企业微信没有的 即需要删除的标签
                    unset($allTag[$v['id']]);
                } else { //若表中没值 需新增分组
                    $createTag[] = [
                        'wx_contact_tag_id'    => $v['id'],
                        'corp_id'              => user()['corpIds'][0],
                        'name'                 => $v['name'],
                        'order'                => $v['order'],
                        'contact_tag_group_id' => $contactGroupId,
                    ];
                }
            }
            unset($v);
        }
        unset($val);

        //更新标签分组
        if (! empty($updateTagGroup)) {
            $updateGroup = $this->contactTagGroupService->updateWorkContactTagGroup($updateTagGroup);
            if (! is_int($updateGroup)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '修改标签分组失败');
            }
        }
        //删除标签分组
        if (! empty($allGroup)) {
            $groupIds = array_column($allGroup, 'id');
            if (! empty($groupIds)) {
                $deleteGroup = $this->contactTagGroupService->deleteWorkContactTagGroups($groupIds);
                if (! is_int($deleteGroup)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签分组失败');
                }
            }
        }
        //添加标签
        if (! empty($createTag)) {
            $createTagRes = $this->contactTagService->createWorkContactTags($createTag);
            if ($createTagRes != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '新增标签失败');
            }
        }
        //更新标签
        if (! empty($updateTag)) {
            $res = $this->contactTagService->updateWorkContactTag($updateTag);
            if (! is_int($res)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '修改标签失败');
            }
        }
        //删除标签
        if (! empty($allTag)) {
            $tagIds = array_column($allTag, 'id');
            if (! empty($tagIds)) {
                $deleteRes = $this->contactTagService->deleteWorkContactTags($tagIds);
                if (! is_int($deleteRes)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签失败');
                }
            }
        }
    }

    /**
     * 更新标签同步时间.
     */
    private function updateSynTime()
    {
        //查询当前企业有没有同步客户的时间
        $workUpdateTime = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType(user()['corpIds'], (int) Type::TAG);
        //如果查到 就更新
        if (! empty($workUpdateTime)) {
            $data['last_update_time'] = date('Y-m-d H:i:s');
            $id                       = end($workUpdateTime)['id'];
            $updateRes                = $this->workUpdateTime->updateWorkUpdateTimeById((int) $id, $data);
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '更新标签同步时间失败');
            }
        } else {
            //如果没有新增
            $params = [
                'corp_id'          => user()['corpIds'][0],
                'type'             => Type::TAG,
                'last_update_time' => date('Y-m-d H:i:s'),
            ];

            $createRes = $this->workUpdateTime->createWorkUpdateTime($params);
            if (! is_int($createRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加标签同步时间失败');
            }
        }
    }
}
