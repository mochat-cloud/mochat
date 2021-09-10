<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\QueueService;

use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 修改客户备注信息
 * Class UpdateRemarkApply.
 */
class UpdateRemarkApply
{
    use AppTrait;

    /**
     * @param $data
     * @param $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handle($data, $corpId): void
    {
        ## 获取企业微信授信信息
        $corp = make(CorpContract::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //修改客户备注信息
        $res = $ecClient->remark($data);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
    }
}
