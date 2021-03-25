<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WeWork;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkAgentServiceInterface;
use EasyWeChat\Work\Application;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;

trait AppTrait
{
    /**
     * 根据微信企业ID获取easyWechat实例.
     * @param int|string $corpId 微信corpid或企业表corpId
     * @param string $type ...
     * @return Application ...
     */
    public function wxApp($corpId, string $type = 'user'): Application
    {
        if (is_int($corpId)) {
            $corMethod = 'getCorpById';
        } else {
            $corMethod = 'getCorpsByWxCorpId';
        }
        $corp = make(CorpServiceInterface::class)->{$corMethod}($corpId, [
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
    public function wxAgentApp($agentId): Application
    {
        $agentFunc = is_int($agentId) ? 'getWorkAgentById' : 'getWorkAgentByWxAgentId';
        $agent     = make(WorkAgentServiceInterface::class)->{$agentFunc}($agentId, [
            'id', 'wx_secret', 'corp_id',
        ]);
        if (empty($agent)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, sprintf('无该应用:[%s]', $agentId));
        }
        ## 根据corpId 查 wxCorpid
        $corp = make(CorpServiceInterface::class)->getCorpById($agent['corpId'], [
            'id', 'wx_corpid',
        ]);

        $config = [
            'corp_id' => $corp['wxCorpid'],
            'secret'  => $agent['wxSecret'],
        ];

        return make(WeWork::class)->app($config);
    }
}
