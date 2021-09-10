<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Corp\Logic;

use EasyWeChat\Work\Application;
use MoChat\App\Corp\Utils\WeWorkFactory;

trait AppTrait
{
    /**
     * 根据微信企业ID获取easyWechat实例.
     *
     * @param int|string $corpId 微信corpid或企业表corpId
     */
    public function wxApp($corpId, string $type = 'user'): Application
    {
        $weWorkFactory = make(WeWorkFactory::class);
        if ($type === 'user') {
            return $weWorkFactory->getUserApp($corpId);
        }

        return $weWorkFactory->getContactApp($corpId);
    }

    /**
     * 获取三方应用或自建应用实例.
     *
     * @param int|string $agentId
     */
    public function wxAgentApp($agentId): Application
    {
        $weWorkFactory = make(WeWorkFactory::class);
        return $weWorkFactory->getAgentApp($agentId);
    }
}
