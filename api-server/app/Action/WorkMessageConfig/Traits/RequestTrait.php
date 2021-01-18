<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessageConfig\Traits;

use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

trait RequestTrait
{
    /**
     * 属性替换.
     * @return array|string[] ...
     */
    public function attributes(): array
    {
        return [
            'id'                    => 'ID',
            'socialCode'            => '企业代码',
            'chatAdmin'             => '企业负责人',
            'chatAdminPhone'        => '企业负责人手机号',
            'chatAdminIdcard'       => '企业负责人身份证',
            'chatStatus'            => '会话存档状态',
            'chatApplyStatus'       => '会话存档申请进度',
            'chatRsaKey'            => 'rsa密钥',
            'chatWhitelistIp'       => '会话服务器白名单',
            'chatRsaKey.publicKey'  => '公钥',
            'chatRsaKey.privateKey' => '私钥',
            'chatRsaKey.version'    => 'rsa密钥版本号',
        ];
    }

    /**
     * 验证场景.
     * @return array|array[] 场景规则
     */
    public function scene(): array
    {
        return [
            'store'      => ['socialCode', 'chatAdmin', 'chatAdminPhone', 'chatAdminIdcard', 'chatApplyStatus'],
            'stepSecond' => ['chatApplyStatus'],
            'stepThird'  => [
                'chatWhitelistIp', 'chatRsaKey', 'chatSecret', 'chatStatus', 'chatApplyStatus',
                'chatRsaKey.publicKey', 'chatRsaKey.privateKey', 'chatRsaKey.version',
            ],
        ];
    }

    /**
     * 扩展验证
     * @param array $inputs 请求参数
     * @param string $validateScene 场景
     */
    public function validateExtend(array $inputs, string $validateScene): void
    {
        if ($validateScene === 'store') {
            return;
        }

        ## 当前企业
        $corpIds = user('corpIds');
        if (count($corpIds) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请选择一个企业，再进行操作');
        }
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'socialCode'            => 'required|alpha_num|size:18',
            'chatAdmin'             => 'required',
            'chatAdminPhone'        => 'required|phone',
            'chatAdminIdcard'       => 'required|alpha_num|size:18',
            'chatStatus'            => 'integer|in:0,1,2',
            'chatApplyStatus'       => 'integer|in:0,1,2,3,4',
            'chatSecret'            => 'required',
            'chatWhitelistIp'       => 'required',
            'chatRsaKey'            => 'required',
            'chatRsaKey.publicKey'  => 'required',
            'chatRsaKey.privateKey' => 'required',
            'chatRsaKey.version'    => 'required',
        ];
    }
}
