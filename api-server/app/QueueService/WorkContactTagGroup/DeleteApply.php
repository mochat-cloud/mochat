<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContactTagGroup;

use App\Contract\CorpServiceInterface;
use App\Logic\WeWork\AppTrait;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 删除客户标签分组
 * Class DeleteApply.
 */
class DeleteApply
{
    use AppTrait;

    /**
     * @param $params
     * @param $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($params, $corpId): void
    {
        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //删除分组
        $res = $ecClient->deleteCorpTag($params['tag_id'], $params['group_id']);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
    }
}
