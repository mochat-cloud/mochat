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
use App\Logic\WorkContactTag\StoreCallBackLogic;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 添加客户标签回调
 * Class StoreCallBackApply.
 */
class StoreCallBackApply
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

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $wxResponse): void
    {
        //查询企业id
        $this->corp = $this->getCorp($wxResponse['ToUserName']);
        if (empty($this->corp)) {
            return;
        }

        //若创建的是标签
        if ($wxResponse['TagType'] == 'tag') {
            $this->handleTag($wxResponse);
        }
        //若创建的是标签组
        if ($wxResponse['TagType'] == 'tag_group') {
            $this->handleTagGroup($wxResponse);
        }
    }

    /**
     * 若创建的是标签组.
     * @param $wxResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleTagGroup($wxResponse)
    {
        $ecClient = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact;
        //获取企业标签库
        $res = $ecClient->getCorpTags();
        if ($res['errcode'] != 0) {
            //记录日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '获取企业标签库错误', date('Y-m-d H:i:s'), json_encode($res)));
        }

        make(StoreCallBackLogic::class)->handle($res, $this->corp);
    }

    /**
     * 若创建的是标签.
     * @param $wxResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleTag($wxResponse)
    {
        $tag      = make(WorkContactTagServiceInterface::class);
        $tagGroup = make(WorkContactTagGroupServiceInterface::class);

        $ecClient = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact;
        //通过标签ID获取标签详情
        $res = $ecClient->getCorpTags([$wxResponse['Id']]);
        if ($res['errcode'] != 0) {
            //记录日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '获取标签详情错误', date('Y-m-d H:i:s'), $wxResponse['Id'], json_encode($res)));
        }

        $tagDetail    = $res['tag_group'][0];
        $tagGroupData = [
            'corp_id'     => $this->corp['id'],
            'wx_group_id' => $tagDetail['group_id'],
            'group_name'  => $tagDetail['group_name'],
            'order'       => $tagDetail['order'],
        ];

        //查询分组id
        $tagGroupRes = $tagGroup->getWorkContactTagGroupByWxGroupId($tagDetail['group_id']);
        if (! empty($tagGroupRes)) {
            $tagGroupId = $tagGroupRes['id'];
        } else {
            $tagGroupId = $tagGroup->createWorkContactTagGroup($tagGroupData);
            if (! is_int($tagGroupId)) {
                $this->logger->error('新建标签回调失败', $tagGroupData);
                throw new CommonException(ErrorCode::SERVER_ERROR, '新增分组失败');
            }
        }

        //查询该分组下有无此标签
        $tagInfo = $tag->getWorkContactTagByGroupIdName($tagGroupId, $tagDetail['tag'][0]['name']);
        if (! empty($tagInfo)) {
            return;
        }

        //新增标签
        $tagData = [
            'corp_id'              => $this->corp['id'],
            'wx_contact_tag_id'    => $tagDetail['tag'][0]['id'],
            'name'                 => $tagDetail['tag'][0]['name'],
            'order'                => $tagDetail['tag'][0]['order'],
            'contact_tag_group_id' => $tagGroupId,
        ];
        $tagRes = $tag->createWorkContactTag($tagData);
        if (! is_int($tagRes)) {
            $this->logger->error('新建标签回调失败', $tagGroupData);
            throw new CommonException(ErrorCode::SERVER_ERROR, '新增标签失败');
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
