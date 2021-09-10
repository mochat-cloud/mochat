<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\OfficialAccount\Logic;

use EasyWeChat\Factory;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Utils\Url;

class AuthLogic
{
    /**
     * @Inject
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;

    public function handle(string $code, string $appId, string $target, array $officialAccountInfo, string $redirectUriPrefix, RequestInterface $request)
    {
        $target = $this->getTarget($target);
        // 有code值 是授权回调
        if (! empty($code)) {
            return urldecode($this->authCallback($code, $appId, $target, $request));
        }

        $weChatUser = $this->session->get('wechat_user_' . $officialAccountInfo['id']);

        // 已登录，直接跳转
        if (! empty($weChatUser)) {
            return urldecode($target);
        }
        // 未登录，跳转至授权
        $redirectUri = $redirectUriPrefix . '?target=' . $target;
        return $this->getOAuthRedirectUrl($officialAccountInfo['authorizerAppid'], $officialAccountInfo['appid'], $redirectUri);
    }

    protected function getTarget($target): string
    {
        if (strpos($target, 'http') === false) {
            $target = Url::getOperationBaseUrl() . $target;
        }
        return urlencode($target);
    }

    protected function authCallback(string $code, string $appId, string $target, RequestInterface $request)
    {
        $config = config('framework.wechat_open_platform');
        ## EasyWeChat
        $openPlatform = Factory::openPlatform($config);
        $openPlatform = rebind_app($openPlatform, $request);
        $result = $openPlatform->getAuthorizer($appId);
        if (! empty($result['authorization_info'])) {
            $officialAccount = $openPlatform->officialAccount($appId, $result['authorization_info']['authorizer_refresh_token']);
            $officialAccount = rebind_app($officialAccount, $request);
            $user = $officialAccount->oauth->userFromCode($code);
            $user = $user->toArray();
            $this->session->set('wechat_user_' . $appId, $user);
            return ! empty($target) ? $target : Url::getOperationBaseUrl();
        }
        return Url::getOperationBaseUrl();
    }

    protected function getOAuthRedirectUrl(string $appId, string $componentAppId, string $redirectUri): string
    {
        $query = [
            'appid' => $appId,
            'component_appid' => $componentAppId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo',
            'state' => '',
        ];

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($query) . '#wechat_redirect';
    }
}
