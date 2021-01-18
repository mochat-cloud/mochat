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
    protected function wxApp($corpId, string $type = 'user'): Application
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
}
