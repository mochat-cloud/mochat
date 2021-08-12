<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace MoChat\Plugin\ShopCode\Action\Operation;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
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
     * @var OfficialAccountSetContract
     */
    protected $officialAccountSetService;

    /**
     * @Inject
     * @var OfficialAccountContract
     */
    protected $officialAccountService;

    /**
     * @RequestMapping(path="/operation/shopCode/openUserInfo", methods="GET")
     * @throws \JsonException
     */
    public function handle()
    {
        $info = $this->getOfficialAccountByInfo();
        dump($this->request->getCookieParams());
        dump($this->session->getId());
        dump($info['authorizerAppid']);
        $weChatUser = $this->session->get('wechat_user_' . $info['authorizerAppid']);
        dump($weChatUser);
        if (!empty($weChatUser)) {
            return $weChatUser;
        }
        return [];
    }

    /**
     * 获取公众号信息
     *
     * @return array
     */
    protected function getOfficialAccountByInfo()
    {
        $corpId = (int)$this->request->input('id');

        if ($corpId === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '活动不存在');
        }

        $type = 3;
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (!empty($set)) {
            return $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['id', 'appid', 'authorizer_appid']);
        } else {
            return $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['id', 'appid', 'authorizer_appid']);
        }
    }
}
