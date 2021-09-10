<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\QueueService\Tag;

use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagGroupContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 删除客户标签
 * Class DeleteApply.
 */
class DeleteApply
{
    use AppTrait;

    /**
     * @var WorkContactTagContract
     */
    private $contactTagService;

    /**
     * @var WorkContactTagGroupContract
     */
    private $contactTagGroupService;

    /**
     * @var WorkContactTagPivotContract
     */
    private $contactTagPivotService;

    /**
     * @param $params
     * @param $contactTagGroupIds
     * @param $tagIds
     * @param $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($params, $contactTagGroupIds, $tagIds, $corpId): void
    {
        $this->contactTagService = make(WorkContactTagContract::class);
        $this->contactTagGroupService = make(WorkContactTagGroupContract::class);
        $this->contactTagPivotService = make(WorkContactTagPivotContract::class);

        ## 获取企业微信授信信息
        $corp = make(CorpContract::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //删除标签
        $res = $ecClient->deleteCorpTag($params['tag_id'], $params['group_id']);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }

        //查询该分组下是否还有标签（没有标签的话企业微信会自动删除该分组）
        $groupInfo = $this->contactTagService->getWorkContactTagsByGroupIds($contactTagGroupIds);

        //还有标签的分组
        $groupIds = array_unique(array_column($groupInfo, 'contactTagGroupId'));

        //求差集 结果为没标签的分组id
        $groupIdDiff = array_diff($contactTagGroupIds, $groupIds);

        //若该分组下没有标签 删除该分组
        if (! empty($groupIdDiff)) {
            $deleteRes = $this->contactTagGroupService
                ->deleteWorkContactTagGroups($groupIdDiff);

            if (! is_int($deleteRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '分组删除失败');
            }
        }

        //删除客户标签
        $contactTag = $this->contactTagPivotService->deleteWorkContactTagPivotsByTagId($tagIds);
        if (! is_int($contactTag)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签删除失败');
        }
    }
}
