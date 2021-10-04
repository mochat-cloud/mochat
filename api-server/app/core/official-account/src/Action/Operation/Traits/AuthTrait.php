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
use MoChat\App\OfficialAccount\Logic\AuthLogic;
use MoChat\App\Utils\Url;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * 公众号授权跳转.
 * @Controller
 */
trait AuthTrait
{
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
     * @Inject
     * @var AuthLogic
     */
    private $authLogic;

    protected function execute(): Psr7ResponseInterface
    {
        $target = (string) $this->request->input('target');
        $code = (string) $this->request->input('code');
        $appId = (string) $this->request->input('appid');

        $officialAccountInfo = [];
        if (empty($code)) {
            $officialAccountInfo = $this->getOfficialAccountInfo();
        }

        $redirectUriPrefix = Url::getOperationBaseUrl() . '/auth/' . $this->getModuleName();
        $redirectUri = $this->authLogic->handle(
            $code,
            $appId,
            $target,
            $officialAccountInfo,
            $redirectUriPrefix,
            $this->request
        );
        return $this->response->redirect($redirectUri);
    }

    protected function getModuleName()
    {
        return '';
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
     * 获取公众号信息.
     *
     * @return array
     */
    protected function getOfficialAccountInfo()
    {
        $corpId = $this->getCorpId();

        if ($corpId === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '数据不存在');
        }

        $type = $this->getType();
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (! empty($set)) {
            $info = $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['id', 'appid', 'authorizer_appid']);
            // 配置的公众号未找到时，获取默认的
            if (! empty($info)) {
                return $info;
            }
        }

        $info = $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['id', 'appid', 'authorizer_appid']);

        if (empty($info)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '公众号配置错误或不存在');
        }
        return $info;
    }
}
