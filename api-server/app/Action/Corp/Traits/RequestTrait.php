<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Corp\Traits;

use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;

trait RequestTrait
{
    /**
     * @Inject
     * @var WeWork
     */
    protected $wxClient;

    /**
     * 验证场景.
     * @return array|array[] 场景规则
     */
    public function scene(): array
    {
        return [
            'store'  => ['corpName', 'wxCorpId', 'employeeSecret', 'contactSecret'],
            'update' => ['corpId', 'corpName', 'wxCorpId', 'employeeSecret', 'contactSecret'],
        ];
    }

    /**
     * 验证规则.
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId'         => 'required|integer|min:0|bail',
            'corpName'       => 'required|string|min:1|bail',
            'wxCorpId'       => 'required|string|between:1,18|bail',
            'employeeSecret' => 'required|string|between:1,43|bail',
            'contactSecret'  => 'required|string|between:1,43|bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required'         => '企业授信ID 必填',
            'corpId.integer'          => '企业授信ID 必需为整数',
            'corpId.min'              => '企业授信ID 不可小于1',
            'corpName.required'       => '企业名称 必填',
            'corpName.string'         => '企业名称 必需是字符串类型',
            'corpName.min'            => '企业名称 不可为空',
            'wxCorpId.required'       => '企业ID 必填',
            'wxCorpId.string'         => '企业ID 必需是字符串类型',
            'wxCorpId.between'        => '企业ID 字符串最大长度18',
            'employeeSecret.required' => '通讯录管理secret 必填',
            'employeeSecret.string'   => '通讯录管理secret 必需是字符串类型',
            'employeeSecret.between'  => '通讯录管理secret  字符串最大长度43',
            'contactSecret.required'  => '外部联系人管理secret 必填',
            'contactSecret.string'    => '外部联系人管理secret 必需是字符串类型',
            'contactSecret.between'   => '外部联系人管理secret 字符串最大长度43',
        ];
    }

    /**
     * 其它验证
     * @param array $inputs 请求数据
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function validateExtend(array $inputs): void
    {
        $config = [
            'user' => [
                'corp_id' => $inputs['wxCorpId'],
                'secret'  => $inputs['employeeSecret'],
            ],
            'contact' => [
                'corp_id' => $inputs['wxCorpId'],
                'secret'  => $inputs['contactSecret'],
            ],
        ];
        try {
            $userClient    = $this->wxClient->app($config['user'])->user->get('1');
            $contactClient = $this->wxClient->app($config['contact'])->external_contact->get('1');
        } catch (\EasyWeChat\Kernel\Exceptions\HttpException $e) {
            if ($e->formattedResponse['errcode'] === 40012) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '企业ID无效');
            }
            if ($e->formattedResponse['errcode'] === 40001) {
                $msg                                                    = '无效';
                isset($userClient) || $msg                              = '通讯录管理secret或企业ID' . $msg;
                (isset($userClient) && ! isset($contactClient)) && $msg = '外部联系人管理secret' . $msg;
                throw new CommonException(ErrorCode::INVALID_PARAMS, $msg);
            }
        }
    }
}
