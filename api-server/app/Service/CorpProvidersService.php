<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Service;

use App\Model\Corp;
use MoChat\Framework\Contract\WeWork\AgentConfigurable;
use MoChat\Framework\Contract\WeWork\AppConfigurable;
use MoChat\Framework\Contract\WeWork\ExternalContactConfigurable;
use MoChat\Framework\Contract\WeWork\UserConfigurable;

class CorpProvidersService implements AppConfigurable, UserConfigurable, ExternalContactConfigurable, AgentConfigurable
{
    /**
     * @var \Hyperf\Database\Model\Model
     */
    protected $corpModel;

    /**
     * 企业数据.
     * @var array
     */
    private $corpData;

    public function __construct()
    {
        $this->corpModel = make(Corp::class);
        $this->corpData  = [];
    }

    public function appConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        $corpQuery             = $this->corpModel::query();
        $corpQuery             = isset($wxCorpId) ? $corpQuery->where('wx_corpid', $wxCorpId) : $corpQuery->where('id', '=', $this->finCorpId());
        $corpData              = $corpQuery->first(['*']);
        $corpData || $corpData = collect([]);
        $this->corpData        = $corpData->toArray();

        return [
            'corp_id' => isset($this->corpData['wxCorpid']) ? $this->corpData['wxCorpid'] : '',
            'token'   => isset($this->corpData['token']) ? $this->corpData['token'] : '',
            'aes_key' => isset($this->corpData['encodingAesKey']) ? $this->corpData['encodingAesKey'] : '',
        ];
    }

    public function agentConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return array_merge($this->appConfig($wxCorpId, $agentId), [
            'agent_id' => 1000005,
            'secret'   => 'elh78M0rRdjZkqnpykS4eKS8N2M9TtT42U3jcvWq94g',
        ]);
    }

    public function externalContactConfig(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return array_merge($this->appConfig($wxCorpId, $agentId), [
            'secret' => isset($this->corpData['contactSecret']) ? $this->corpData['contactSecret'] : '',
        ]);
    }

    public function userConfig(?string $wxCorpId = null): array
    {
        return array_merge($this->appConfig($wxCorpId), [
            'secret' => isset($this->corpData['employeeSecret']) ? $this->corpData['employeeSecret'] : '',
        ]);
    }

    public function finCorpId(): int
    {
        $corpId = 0;
        try {
            isset(user()['corpIds']) && $corpId = (int) user()['corpIds'][0];
        } catch (\Throwable $e) {
            if ($e->getCode() == 401) {
                $corpId = 0;
            }
        }
        return $corpId;
    }
}
