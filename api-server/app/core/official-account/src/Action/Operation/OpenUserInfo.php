<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\OfficialAccount\Action\Operation;

use EasyWeChat\Factory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 获取用户基本信息(需授权作用域为 snsapi_userinfo).
 * @Controller
 */
class OpenUserInfo extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @RequestMapping(path="/operation/officialAccount/openUserInfo", methods="get,post")
     * @throws \JsonException
     */
    public function handle()
    {
        ## 参数验证
        $this->validated($this->request->all());
        $code   = $this->request->input('code');
        $appid  = $this->request->input('appid');
        $config = config('framework.wechat_open_platform');
        ## EasyWeChat
        $openPlatform = Factory::openPlatform($config);
        $openPlatform = rebind_app($openPlatform, $this->request);
        $result       = $openPlatform->getAuthorizer($appid);
        if (! empty($result['authorization_info'])) {
            $officialAccount = $openPlatform->officialAccount($appid, $result['authorization_info']['authorizer_refresh_token']);
            $user            = $officialAccount->oauth->userFromCode($code);
            $user            = $user->toArray();
            return $user['raw'];
        }
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'code'  => 'required',
            'appid' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'code.required'  => 'code 必传',
            'appid.required' => 'appid 必传',
        ];
    }
}
