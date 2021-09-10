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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 编辑客户企业标签
 * Class UpdateContactTagApply.
 */
class UpdateContactTagApply
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param $params
     * @param mixed $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($params, $corpId): void
    {
        ## 获取企业微信授信信息
        $corp = make(CorpContract::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;
        //编辑客户企业标签
        $res = $ecClient->markTags($params);
        if ($res['errcode'] !== 0) {
            $this->logger->error(sprintf('%s [%s] %s', '客户打标签失败', date('Y-m-d H:i:s'), json_encode($res, JSON_THROW_ON_ERROR)));
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
    }
}
