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

use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\QueueService\WorkContactTag\StoreApply;
use App\QueueService\WorkContactTag\UpdateApply;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 批量移动标签分组.
 *
 * Class MoveLogic
 */
class MoveLogic
{
    /**
     * @var UpdateApply
     */
    protected $service;

    /**
     * @var StoreApply
     */
    protected $storeService;

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
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $params)
    {
        $tagIds = explode(',', $params['tagId']);

        //查询标签对应的wx_contact_tag_id，标签名称,分组id
        $tagInfo = $this->contactTagService->getWorkContactTagsById($tagIds);
        if (empty($tagInfo)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到标签信息');
        }

        $tagNames = array_column($tagInfo, 'name');
        //查询要移动的分组下是否已经有该标签名称
        $groupTag = $this->contactTagService->getWorkContactTagsByNamesGroupId($tagNames, $params['groupId']);
        if (! empty($groupTag)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '该分组下已有相同标签');
        }

        //移动标签分组
        $updateTagGroup = $this->contactTagService->updateAllByIds($tagIds, ['contact_tag_group_id' => $params['groupId']]);
        if (! is_int($updateTagGroup)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '移动标签失败');
        }

        //同步到企业微信
        $this->synTag($params, $tagInfo);
    }

    /**
     * 同步企业微信
     * @param $params
     * @param $tagInfo
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function synTag($params, $tagInfo)
    {
        $this->service      = make(UpdateApply::class);
        $wxContactTagId     = array_column($tagInfo, 'wxContactTagId');
        $contactTagGroupIds = array_unique(array_column($tagInfo, 'contactTagGroupId'));
        if (! empty($wxContactTagId)) {
            //删除微信标签
            $deleteParams = [
                'tag_id'   => $wxContactTagId,
                'group_id' => [],
            ];

            $this->service->delete($deleteParams, $contactTagGroupIds, user()['corpIds'][0]);
        }
        //若不是移动到未分组下 需将标签添加到新分组内
        if ($params['groupId'] != 0) {
            $tag = [];
            foreach ($tagInfo as &$value) {
                $tag[] = [
                    'name' => $value['name'],
                ];
            }
            unset($value);

            //查询要修改的分组是否有 wx_group_id
            $groupInfo = $this->contactTagGroupService->getWorkContactTagGroupById((int) $params['groupId'], ['wx_group_id', 'group_name']);
            //如果有 将该标签新增到该分组下
            if (! empty($groupInfo['wxGroupId'])) {
                $addParams = [
                    'group_id' => $groupInfo['wxGroupId'],
                    'tag'      => $tag,
                ];
            } else {  //如果没有 新建分组并将标签新增到该分组下
                $addParams = [
                    'group_name' => $groupInfo['groupName'],
                    'tag'        => $tag,
                ];
            }

            //添加到企业微信 同时同步企业微信标签分组
            $this->storeService = make(StoreApply::class);
            $this->storeService->handle($addParams, $params['groupId'], user()['corpIds'][0]);
        }
    }
}
