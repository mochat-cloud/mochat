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
use MoChat\App\WorkContact\Logic\Tag\SynContactTagLogic;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 获取企业标签库
 * Class SyncContactTagApply.
 */
class SyncContactTagApply
{
    use AppTrait;

    /**
     * @param mixed $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($corpId): void
    {
        ## 获取企业微信授信信息
        $corp = make(CorpContract::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //获取企业标签库
        $res = $ecClient->getCorpTags();

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }

        make(SynContactTagLogic::class)->handle($res);
    }
}
