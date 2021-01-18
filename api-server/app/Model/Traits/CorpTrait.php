<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Model\Traits;

trait CorpTrait
{
    public function appConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return [
            'corp_id' => 'ww9d74763485bae574',
            'token'   => 'Yo61t21o9ULjGFZNjlpbYUueBGTMsGg',
            'aes_key' => 'ikuWindSi5wj588cmGEH7D2BWxbGTdlc2okBsY7QzGY',

            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file'  => BASE_PATH . '/runtime/logs/wechat.log',
            ],
        ];
    }

    public function agentConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return array_merge($this->appConfig(), [
            'agent_id' => 1000005,
            'secret'   => 'elh78M0rRdjZkqnpykS4eKS8N2M9TtT42U3jcvWq94g',
        ]);
    }

    public function externalContactConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return array_merge($this->appConfig(), [
            'secret' => 'uTItPEVzxnc_yzXVptEtFP_tHMYzslZObVoHCMJI_XQ',
        ]);
    }

    public function userConfig(?string $wxCorpId = null): array
    {
        return array_merge($this->appConfig(), [
            'secret' => 'UbnxaEeb4qojMz4XRd7vO4f8Q16a4RePtLfCoxmGUQQ',
        ]);
    }
}
