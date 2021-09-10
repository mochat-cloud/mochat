<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Utils;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\WeWorkFinanceSDK\WxFinanceSDK;

class MessageArchiveFactory
{
    /**
     * @var array
     */
    protected $messageArchives = [];

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkMessageConfigContract
     */
    protected $messageConfigService;

    public function make(int $corpId): WxFinanceSDK
    {
        $corp = $this->getCorpInfo($corpId);
        $rsa = json_decode($corp['config']['chatRsaKey'], true);
        return WxFinanceSDK::init([
            'corpid' => $corp['wxCorpid'],
            'secret' => $corp['config']['chatSecret'],
            'private_keys' => [
                $rsa['version'] => $rsa['privateKey'],
            ],
        ]);
    }

    public function get(int $corpId): WxFinanceSDK
    {
        if (isset($this->messageArchives[$corpId]) && $this->messageArchives[$corpId] instanceof WxFinanceSDK) {
            return $this->messageArchives[$corpId];
        }

        return $this->messageArchives[$corpId] = $this->make($corpId);
    }

    private function getCorpInfo(int $corpId): array
    {
        $corpInfo = $this->corpService->getCorpById($corpId, ['id', 'wx_corpid']);
        $corpInfo['config'] = $this->messageConfigService->getWorkMessageConfigByCorpId($corpId, ['id', 'chat_rsa_key', 'chat_secret']);
        return $corpInfo;
    }
}
