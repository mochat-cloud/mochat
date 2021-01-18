<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContact;

use App\Contract\CorpServiceInterface;
use App\Logic\WeWork\AppTrait;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 编辑客户企业标签
 * Class UpdateContactTagApply.
 */
class UpdateContactTagApply
{
    use AppTrait;

    /**
     * @param $params
     * @param mixed $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($params, $corpId): void
    {
        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //编辑客户企业标签
        $res = $ecClient->markTags($params);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
    }
}
