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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
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
     * @Inject()
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;

    /**
     * @Inject
     * @var OfficialAccountContract
     */
    protected $officialAccountService;

    /**
     * @RequestMapping(path="/operation/officialAccount/openUserInfo", methods="GET")
     * @throws \JsonException
     */
    public function handle()
    {
        $officialAccountId = $this->request->input('officialAccountId');

        $info = $this->officialAccountService->getOfficialAccountById($officialAccountId, ['authorizer_appid']);
        $weChatUser = $this->session->get('wechat_user_' . $info['authorizerAppid']);
        if (!empty($weChatUser)) {
            return $weChatUser;
        }
        return [];
    }
}
