<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Listener\Tag;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagGroupContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkContact\Event\Tag\DeleteTagRawEvent;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Psr\Container\ContainerInterface;

/**
 * 删除标签事件监听器.
 *
 * @Listener
 */
class DeleteTagRawListener implements ListenerInterface
{
    use AppTrait;

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

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

    public function listen(): array
    {
        return [
            DeleteTagRawEvent::class,
        ];
    }

    /**
     * @param DeleteTagRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        //查询企业id
        $this->corp = $this->getCorp($message['ToUserName']);
        if (empty($this->corp)) {
            return;
        }

        //若删除的是标签
        if ($message['TagType'] == 'tag') {
            $this->handleTag($message);
        }
        //若删除的是标签组
        if ($message['TagType'] == 'tag_group') {
            $this->handleTagGroup($message);
        }
    }

    /**
     * 若删除的是标签组.
     * @param $message
     */
    private function handleTagGroup($message)
    {
        $tagGroup = make(WorkContactTagGroupContract::class);
        $tag = make(WorkContactTagContract::class);
        $contactTagPivotService = make(WorkContactTagPivotContract::class);

        //查询本地有无此分组
        $tagGroupInfo = $tagGroup->getWorkContactTagGroupByWxGroupId($message['Id'], ['id']);
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
            $this->logger->error('删除标签分组回调失败', $message);
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除标签分组失败');
        }

        //删除分组下的标签
        $tagRes = $tag->deleteWorkContactTags($tagIds);
        if (! is_int($tagRes)) {
            $this->logger->error('删除标签分组回调失败', $message);
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
     * @param $message
     */
    private function handleTag($message)
    {
        $tag = make(WorkContactTagContract::class);
        $contactTagPivotService = make(WorkContactTagPivotContract::class);

        //查询标签信息
        $tagInfo = $tag->getWorkContactTagsByWxTagId($message['Id'], ['id']);

        if (empty($tagInfo)) {
            return;
        }

        //删除标签
        $tagRes = $tag->deleteWorkContactTag((int) $tagInfo['id']);

        if (! is_int($tagRes)) {
            $this->logger->error('删除标签回调失败', $message);
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
        $corp = make(CorpContract::class);

        $res = $corp->getCorpsByWxCorpId($wxCorpId, ['id']);
        if (empty($res)) {
            return [];
        }

        return $res;
    }
}
