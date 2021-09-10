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
use MoChat\App\WorkContact\Event\Tag\UpdateTagRawEvent;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Psr\Container\ContainerInterface;

/**
 * 更新标签事件监听器.
 *
 * @Listener
 */
class UpdateTagRawListener implements ListenerInterface
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
            UpdateTagRawEvent::class,
        ];
    }

    /**
     * @param UpdateTagRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        //查询企业id
        $this->corp = $this->getCorp($message['ToUserName']);
        if (empty($this->corp)) {
            return;
        }

        //若修改的是标签
        if ($message['TagType'] == 'tag') {
            $this->handleTag($message);
        }
        //若修改的是标签组
        if ($message['TagType'] == 'tag_group') {
            $this->handleTagGroup($message);
        }
    }

    /**
     * 若修改的是标签组.
     * @param $message
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleTagGroup($message)
    {
        $ecClient = $this->wxApp($message['ToUserName'], 'contact')->external_contact;
        //获取企业标签库
        $res = $ecClient->getCorpTags();
        if ($res['errcode'] != 0) {
            //记录日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '获取企业标签库错误', date('Y-m-d H:i:s'), json_encode($res)));
        }

        $this->updateTagGroup($res);
    }

    /**
     * 若修改的是标签.
     * @param $message
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleTag($message)
    {
        $tag = make(WorkContactTagContract::class);

        $ecClient = $this->wxApp($message['ToUserName'], 'contact')->external_contact;
        //通过标签ID获取标签详情
        $res = $ecClient->getCorpTags([$message['Id']]);
        if ($res['errcode'] != 0) {
            //记录日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '获取标签详情错误', date('Y-m-d H:i:s'), $message['Id'], json_encode($res)));
        }

        $tagDetail = $res['tag_group'][0];

        $data = [
            'name' => $tagDetail['tag'][0]['name'],
            'order' => $tagDetail['tag'][0]['order'],
        ];
        //修改标签
        $updateTagRes = $tag->updateWorkContactTagByWxTagId($tagDetail['tag'][0]['id'], $data);

        if (! is_int($updateTagRes)) {
            $this->logger->error('修改标签回调失败', $tagDetail);
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改标签失败');
        }
    }

    /**
     * 更新标签组.
     * @param $res
     */
    private function updateTagGroup($res)
    {
        $tagGroup = make(WorkContactTagGroupContract::class);
        //查询表中所有分组
        $allGroup = $tagGroup->getWorkContactTagGroupsByCorpId([$this->corp['id']]);
        if (empty($allGroup)) {
            return;
        }

        $allGroup = array_column($allGroup, null, 'wxGroupId');
        $updateTagGroup = [];

        foreach ($res['tag_group'] as &$val) {
            //若表中有值 需更新
            if (isset($allGroup[$val['group_id']])) {
                $updateTagGroup[] = [
                    'id' => $allGroup[$val['group_id']]['id'],
                    'wx_group_id' => $val['group_id'],
                    'group_name' => $val['group_name'],
                    'order' => $val['order'],
                ];
            }
        }
        unset($val);

        //更新标签分组
        if (! empty($updateTagGroup)) {
            $updateGroup = $tagGroup->updateWorkContactTagGroup($updateTagGroup);
            if (! is_int($updateGroup)) {
                $this->logger->error('修改标签分组回调失败', $res);
                throw new CommonException(ErrorCode::SERVER_ERROR, '修改标签分组失败');
            }
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
