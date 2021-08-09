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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use MoChat\App\Corp\Contract\CorpContract;
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
use Psr\Container\ContainerInterface;
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
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @RequestMapping(path="/operation/officialAccount/authRedirect/", methods="get,post")
     * @throws \JsonException
     */
    public function handle(ResponseInterface $response): Psr7ResponseInterface
    {
        $type   = (int) $this->request->input('modularType');
        $id     = (int) $this->request->input('id');
        $corpId = $this->getCorpId($type, $id, (int) $this->request->input('corpId'));

        if ($type === 1) {
            $id = 0;
        }

        if ($corpId === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '活动不存在');
        }
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (! empty($set)) {
            $info = $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['appid', 'authorizer_appid']);
        } else {
            $info = $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['appid', 'authorizer_appid']);
        }
        if ($type === 6 && $id > 0) {
            $officialAccountId = $this->roomFissionService->getRoomFissionById($id, ['official_account_id']);
            $info              = $this->officialAccountService->getOfficialAccountById($officialAccountId['officialAccountId'], ['appid', 'authorizer_appid']);
        }

        $params                    = $this->request->getQueryParams();
        $params['component_appid'] = $info['appid'];
        $state                     = urldecode(http_build_query($params));
        $redirectUrl               = $this->getOAuthRedirectUrl($info['authorizerAppid'], Url::getOperationBaseUrl(), $state);

        return $response->redirect($redirectUrl);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'modularType' => 'required',
            'id'          => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'modularType.required' => 'modularType 必传',
            'id.required'          => 'id 必传',
        ];
    }

    protected function getOAuthRedirectUrl(string $appId, string $redirectUri, string $state)
    {
        $params = [
            'appid'         => $appId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'snsapi_userinfo',
            'state'         => $state,
        ];

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($params) . '#wechat_redirect';
    }

    private function getCorpId(int $type, int $id, int $targetCorpId): int
    {
        $corpId = 0;
        switch ($type) {
            case 1://群打卡
                $info   = $this->clockInService->getClockInById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 2://抽奖活动
                $info   = $this->lotteryService->getLotteryById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 3://门店活码
                $corpId = $targetCorpId;
                break;
            case 4://互动雷达
                $info   = $this->radarService->getRadarById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 5://任务宝
                $info   = $this->workFissionService->getWorkFissionById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            case 6://群裂变
                $info   = $this->roomFissionService->getRoomFissionById($id, ['corp_id']);
                $corpId = empty($info) ? 0 : $info['corpId'];
                break;
            default:
                $corp   = $this->corpService->getCorps();
                $corpId = $corp[0]['id'];
        }
        return $corpId;
    }
}
