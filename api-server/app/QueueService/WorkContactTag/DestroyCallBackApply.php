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
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 删除客户标签回调
 * Class DestroyCallBackApply.
 */
class DestroyCallBackApply
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * 企业.
     * @var array
     */
    private $corp;

    public function handle(array $wxResponse): void
    {
        //查询企业id
        $this->corp = $this->getCorp($wxResponse['ToUserName']);
        if (empty($this->corp)) {
            return;
        }

        //若删除的是标签
        if ($wxResponse['TagType'] == 'tag') {
            $this->handleTag($wxResponse);
        }
        //若删除的是标签组
        if ($wxResponse['TagType'] == 'tag_group') {
            $this->handleTagGroup($wxResponse);
        }
    }

    /**
     * 若删除的是标签组.
     * @param $wxResponse
     */
    private function handleTagGroup($wxResponse)
    {
        $tagGroup               = make(WorkContactTagGroupServiceInterface::class);
        $tag                    = make(WorkContactTagServiceInterface::class);
        $contactTagPivotService = make(WorkContactTagPivotServiceInterface::class);

        //查询本地有无此分组
        $tagGroupInfo = $tagGroup->getWorkContactTagGroupByWxGroupId($wxResponse['Id'], ['id']);
        if (empty($tagGroupInfo)) {
            return;
        }

        //查询分组下的标签id
        $tagInfo = $tag->getWorkContactTagsByGroupIds([$tagGroupInfo['id']], ['id']);
        if (empty($tagInfo)) {
            return;
        }
        //标签id
        $tagIds = array_column($tagInfo, 'id');

        //删除分组
        $tagGroupRes = $tagGroup->deleteWorkContactTagGroup((int) $tagGroupInfo['id']);
        if (! is_int($tagGroupRes)) {
            $this->logger->error('删除标签分组回调失败', $wxResponse);
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签分组失败');
        }

        //删除分组下的标签
        $tagRes = $tag->deleteWorkContactTags($tagIds);
        if (! is_int($tagRes)) {
            $this->logger->error('删除标签分组回调失败', $wxResponse);
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签失败');
        }
        //删除客户标签
        $contactTag = $contactTagPivotService->deleteWorkContactTagPivotsByTagId($tagIds);
        if (! is_int($contactTag)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签删除失败');
        }
    }

    /**
     * 若删除的是标签.
     * @param $wxResponse
     */
    private function handleTag($wxResponse)
    {
        $tag                    = make(WorkContactTagServiceInterface::class);
        $contactTagPivotService = make(WorkContactTagPivotServiceInterface::class);

        //查询标签信息
        $tagInfo = $tag->getWorkContactTagsByWxTagId($wxResponse['Id'], ['id']);

        if (empty($tagInfo)) {
            return;
        }

        //删除标签
        $tagRes = $tag->deleteWorkContactTag((int) $tagInfo['id']);

        if (! is_int($tagRes)) {
            $this->logger->error('删除标签回调失败', $wxResponse);
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签失败');
        }

        //删除客户标签
        $contactTag = $contactTagPivotService->deleteWorkContactTagPivotsByTagId([$tagInfo['id']]);
        if (! is_int($contactTag)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签删除失败');
        }
    }

    /**
     * 获取企业id.
     * @param $wxCorpId
     * @return array
     */
    private function getCorp($wxCorpId)
    {
        $corp = make(CorpServiceInterface::class);

        $res = $corp->getCorpsByWxCorpId($wxCorpId, ['id']);
        if (empty($res)) {
            return [];
        }

        return $res;
    }
}
