<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace MoChat\App\OfficialAccount\Action\Operation\Traits;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 获取用户基本信息(需授权作用域为 snsapi_userinfo).
 * @Controller
 */
trait OpenUserInfoTrait
{
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

    public function execute()
    {
        $info = $this->getOfficialAccountInfo();
        $weChatUser = $this->session->get('wechat_user_' . $info['authorizerAppid']);
        if (!empty($weChatUser)) {
            return $weChatUser['raw'];
        }
        return [];
    }

    protected function getType(): int
    {
        return 0;
    }

    protected function getCorpId(): int
    {
        return 0;
    }

    /**
     * 获取公众号信息
     *
     * @return array
     */
    protected function getOfficialAccountInfo()
    {
        $corpId = $this->getCorpId();

        if ($corpId === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '活动不存在');
        }

        $type = $this->getType();
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (!empty($set)) {
            return $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['id', 'appid', 'authorizer_appid']);
        } else {
            return $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['id', 'appid', 'authorizer_appid']);
        }
    }
}
