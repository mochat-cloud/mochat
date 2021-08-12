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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Radar\Contract\RadarContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * 公众号授权跳转.
 * @Controller
 */
class AuthRedirect extends AbstractAction
{
    /**
     * @Inject
     * @var ClockInContract
     */
    protected $clockInService;

    /**
     * @Inject
     * @var LotteryContract
     */
    protected $lotteryService;

    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

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
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject()
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;

    /**
     * @RequestMapping(path="/operation/officialAccount/authRedirect/", methods="get,post")
     * @throws \JsonException
     */
    public function handle(ResponseInterface $response): Psr7ResponseInterface
    {
        // 有code值 是授权回调
        if ($this->request->has('code') && !empty($code = $this->request->input('code'))) {
            return $this->handleOAuthCallback($code);
        }

        $officialAccountId = $this->request->input('oaId');
        $officialAccountInfo = $this->getOfficialAccountByInfo($officialAccountId);
        $targetUrl = $this->getTargetUrl();
        $weChatUser = $this->session->get('wechat_user_'.$officialAccountId);

        // 已登录，直接跳转
        if (!empty($weChatUser)) {
            return $this->response->redirect($targetUrl);
        }

        // 未登录，跳转至授权
        return $response->redirect($this->getOAuthRedirectUrl($officialAccountInfo['authorizerAppid'], $officialAccountInfo['appid'], $targetUrl));
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'moduleType' => 'required',
            'id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'moduleType.required' => 'moduleType 必传',
            'id.required' => 'id 必传',
        ];
    }

    protected function handleOAuthCallback(string $code)
    {
        $appId = $this->request->input('appid');
        $config = config('framework.wechat_open_platform');
        ## EasyWeChat
        $openPlatform = Factory::openPlatform($config);
        $openPlatform = rebind_app($openPlatform, $this->request);
        $result = $openPlatform->getAuthorizer($appId);
        if (!empty($result['authorization_info'])) {
            $officialAccount = $openPlatform->officialAccount($appId, $result['authorization_info']['authorizer_refresh_token']);
            $officialAccount = rebind_app($officialAccount, $this->request);
            $user = $officialAccount->oauth->userFromCode($code);
            $user = $user->toArray();
            $this->session->set('wechat_user_' . $appId, $user);
            $target = $this->request->input('target');
            if (!empty($target)) {
                $targetUrl = $target;
            } else {
                $targetUrl = $this->session->get('target_url', Url::getOperationBaseUrl());
            }

            return $this->response->redirect($targetUrl);
        }
        return [];
    }

    protected function getTargetUrl(): string
    {
        $target = $this->request->input('target', '');
        if (false === strpos($target, 'http')) {
            $target = Url::getOperationBaseUrl() . $target;
        }
        $this->session->set('target_url', $target);
        return $target;
    }

    protected function getOAuthRedirectUrl(string $appId, string $componentAppId, string $targetUrl): string
    {
        $query = [
            'appid' => $appId,
            'component_appid' => $componentAppId,
            'redirect_uri' => Url::getOperationBaseUrl() . '/auth?target='.$targetUrl,
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo',
            'state' => '',
        ];

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($query) . '#wechat_redirect';
    }

    protected function getOfficialAccountByInfo(int $officialAccountId)
    {
        return $this->officialAccountService->getOfficialAccountById($officialAccountId, ['appid', 'authorizer_appid']);
    }

    protected function getOfficialAccountInfoByTypeId(int $type, int $id): array
    {
        $corpId = $this->getCorpId($type, $id);

        if (in_array($type, [3, 4, 5])) {
            $type = 3;
        }

        if ($corpId === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '活动不存在');
        }

        if ($type === 8 && $id > 0) {
            $officialAccountId = $this->roomFissionService->getRoomFissionById($id, ['official_account_id']);
            $info = $this->officialAccountService->getOfficialAccountById($officialAccountId['officialAccountId'], ['appid', 'authorizer_appid']);
            return $info;
        }

        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (!empty($set)) {
            $info = $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['appid', 'authorizer_appid']);
        } else {
            $info = $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['appid', 'authorizer_appid']);
        }

        return $info;
    }

    private function getCorpId(int $type, int $id): int
    {
        switch ($type) {
            case 1://群打卡
                $info = $this->clockInService->getClockInById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 2://抽奖活动
                $info = $this->lotteryService->getLotteryById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 3: // 店主活码
            case 4: // 门店群活码
            case 5: // 城市群活码
                $corpId = $id;
                break;
            case 6://互动雷达
                $info = $this->radarService->getRadarById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 7://任务宝
                $info = $this->workFissionService->getWorkFissionById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 8://群裂变
                $info = $this->roomFissionService->getRoomFissionById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            default:
//                $corp = $this->corpService->getCorps();
//                $corpId = $corp[0]['id'];
                $corpId = 0;
        }
        return $corpId;
    }
}
