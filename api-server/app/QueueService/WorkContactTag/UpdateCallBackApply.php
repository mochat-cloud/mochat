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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 编辑客户标签回调
 * Class UpdateCallBackApply.
 */
class UpdateCallBackApply
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

        //若修改的是标签
        if ($wxResponse['TagType'] == 'tag') {
            $this->handleTag($wxResponse);
        }
        //若修改的是标签组
        if ($wxResponse['TagType'] == 'tag_group') {
            $this->handleTagGroup($wxResponse);
        }
    }

    /**
     * 若修改的是标签组.
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

        $this->updateTagGroup($res);
    }

    /**
     * 若修改的是标签.
     * @param $wxResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleTag($wxResponse)
    {
        $tag = make(WorkContactTagServiceInterface::class);

        $ecClient = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact;
        //通过标签ID获取标签详情
        $res = $ecClient->getCorpTags([$wxResponse['Id']]);
        if ($res['errcode'] != 0) {
            //记录日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '获取标签详情错误', date('Y-m-d H:i:s'), $wxResponse['Id'], json_encode($res)));
        }

        $tagDetail = $res['tag_group'][0];

        $data = [
            'name'  => $tagDetail['tag'][0]['name'],
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
        $tagGroup = make(WorkContactTagGroupServiceInterface::class);
        //查询表中所有分组
        $allGroup = $tagGroup->getWorkContactTagGroupsByCorpId([$this->corp['id']]);
        if (empty($allGroup)) {
            return;
        }

        $allGroup       = array_column($allGroup, null, 'wxGroupId');
        $updateTagGroup = [];

        foreach ($res['tag_group'] as &$val) {
            //若表中有值 需更新
            if (isset($allGroup[$val['group_id']])) {
                $updateTagGroup[] = [
                    'id'          => $allGroup[$val['group_id']]['id'],
                    'wx_group_id' => $val['group_id'],
                    'group_name'  => $val['group_name'],
                    'order'       => $val['order'],
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
        $corp = make(CorpServiceInterface::class);

        $res = $corp->getCorpsByWxCorpId($wxCorpId, ['id']);
        if (empty($res)) {
            return [];
        }

        return $res;
    }
}
