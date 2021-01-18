<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContactTag;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Logic\WeWork\AppTrait;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 编辑客户标签
 * Class UpdateApply.
 */
class UpdateApply
{
    use AppTrait;

    /**
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @param $params
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($params): void
    {
        $this->contactTagService      = make(WorkContactTagServiceInterface::class);
        $this->contactTagGroupService = make(WorkContactTagGroupServiceInterface::class);

        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($params['corpId'], ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //编辑标签
        $res = $ecClient->updateCorpTag($params['id'], $params['name']);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
    }

    /**
     * 修改分组时 删除原分组下的该标签.
     * @param $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(array $params, array $contactTagGroupIds, $corpId): void
    {
        $this->contactTagService      = make(WorkContactTagServiceInterface::class);
        $this->contactTagGroupService = make(WorkContactTagGroupServiceInterface::class);

        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //删除标签
        $res = $ecClient->deleteCorpTag($params['tag_id'], $params['group_id']);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }

        //查询该分组下是否还有标签（没有标签的话企业微信会自动删除该分组）
        $groupInfo = $this->contactTagService->getWorkContactTagsByGroupIds($contactTagGroupIds, ['id']);

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
    }
}
