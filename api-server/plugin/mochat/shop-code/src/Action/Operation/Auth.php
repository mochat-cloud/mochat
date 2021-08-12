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
use MoChat\App\OfficialAccount\Logic\AuthLogic;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * 公众号授权跳转.
 * @Controller
 */
class Auth extends AbstractAction
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
     * @Inject()
     * @var AuthLogic
     */
    private $authLogic;

    /**
     * @RequestMapping(path="/operation/auth/shopCode", methods="get,post")
     */
    public function handle(): Psr7ResponseInterface
    {
        $target = (string) $this->request->input('target');
        $code = (string) $this->request->input('code');
        $appId = (string) $this->request->input('appid');

        $officialAccountInfo = [];
        if (empty($code)) {
            $officialAccountInfo = $this->getOfficialAccountByInfo();
        }

        $redirectUriPrefix = Url::getOperationBaseUrl() . '/auth/shopCode';
        $redirectUri = $this->authLogic->handle($code, $appId, $target, $officialAccountInfo, $redirectUriPrefix, $this->request);
        return $this->response->redirect($redirectUri);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'target' => 'required',
            'corpId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'target.required' => 'target 必传',
            'corpId.required' => 'id 必传',
        ];
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
