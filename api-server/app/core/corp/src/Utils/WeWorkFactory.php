<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Corp\Utils;

use EasyWeChat\Work\Application;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;

class WeWorkFactory
{
    /**
     * @var array
     */
    protected $userApps = [];

    /**
     * @var array
     */
    protected $contactApps = [];

    /**
     * @var array
     */
    protected $agentApps = [];

    /**
     * 获取通讯录应用实例.
     *
     * @param int|string $corpId 企业id
     */
    public function getUserApp($corpId): Application
    {
        if (isset($this->userApps[$corpId]) && $this->userApps[$corpId] instanceof Application) {
            return $this->userApps[$corpId];
        }

        return $this->userApps[$corpId] = $this->makeApp($corpId, 'user');
    }

    /**
     * 获取客户联系应用实例.
     *
     * @param int|string $corpId 企业id
     */
    public function getContactApp($corpId): Application
    {
        if (isset($this->contactApps[$corpId]) && $this->contactApps[$corpId] instanceof Application) {
            return $this->contactApps[$corpId];
        }

        return $this->contactApps[$corpId] = $this->makeApp($corpId, 'contact');
    }

    /**
     * 获取三方应用或自建应用实例.
     *
     * @param int|string $agentId 应用id
     */
    public function getAgentApp($agentId): Application
    {
        if (isset($this->agentApps[$agentId]) && $this->agentApps[$agentId] instanceof Application) {
            return $this->agentApps[$agentId];
        }

        return $this->agentApps[$agentId] = $this->makeAgentApp($agentId);
    }

    /**
     * 解绑通讯录和客户联系应用实例(当企业信息发生变化的时候).
     *
     * @param int|string $corpId
     */
    public function unbindApp($corpId)
    {
        if (isset($this->contactApps[$corpId])) {
            unset($this->contactApps[$corpId]);
        }

        if (isset($this->userApps[$corpId])) {
            unset($this->userApps[$corpId]);
        }
    }

    /**
     * 解绑三方应用实例(当应用信息发生变化的时候).
     *
     * @param int|string $agentId
     */
    public function unbindAgentApp($agentId)
    {
        if (isset($this->agentApps[$agentId])) {
            unset($this->agentApps[$agentId]);
        }
    }

    protected function makeApp($corpId, string $type)
    {
        if (is_int($corpId)) {
            $corMethod = 'getCorpById';
        } else {
            $corMethod = 'getCorpsByWxCorpId';
        }
        $corp = make(CorpContract::class)->{$corMethod}($corpId, [
            'id', 'employee_secret', 'contact_secret', 'wx_corpid',
        ]);
        if (empty($corp)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, sprintf('无该企业:[%s]', $corpId));
        }

        $config['corp_id'] = $corp['wxCorpid'];
        switch ($type) {
            case 'user':
                $config['secret'] = $corp['employeeSecret'];
                break;
            case 'contact':
                $config['secret'] = $corp['contactSecret'];
                break;
            default:
                throw new CommonException(ErrorCode::SERVER_ERROR, sprintf('wxApp方法无此类型:[%s]', $type));
        }

        return make(WeWork::class)->app($config);
    }

    /**
     * 根据企业微信应用id获取信息.
     * @param int|string $agentId
     */
    protected function makeAgentApp($agentId): Application
    {
        $agentFunc = is_int($agentId) ? 'getWorkAgentById' : 'getWorkAgentByWxAgentId';
        $agent = make(WorkAgentContract::class)->{$agentFunc}($agentId, [
            'id', 'wx_secret', 'corp_id',
        ]);
        if (empty($agent)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, sprintf('无该应用:[%s]', $agentId));
        }
        ## 根据corpId 查 wxCorpid
        $corp = make(CorpContract::class)->getCorpById($agent['corpId'], [
            'id', 'wx_corpid',
        ]);

        $config = [
            'corp_id' => $corp['wxCorpid'],
            'secret' => $agent['wxSecret'],
        ];

        return make(WeWork::class)->app($config);
    }
}
