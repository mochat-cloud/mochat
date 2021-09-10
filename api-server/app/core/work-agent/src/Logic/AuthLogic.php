<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\Logic;

use EasyWeChat\Work\Application;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;
use Psr\Log\LoggerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\SimpleJwt\JWTManager;

class AuthLogic
{
    /**
     * @Inject
     * @var WeWork
     */
    private $weWorkClient;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployeeService;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    private $workAgentService;

    /**
     * @Inject
     * @var CorpContract
     */
    private $corpService;

    /**
     * @Inject
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @Inject
     * @var LoggerFactory
     */
    private $loggerFactory;

    public function handle(string $code, int $agentId, string $target)
    {
        try {
            $this->logger = $this->loggerFactory->get('sidebar-auth');

            $target = $this->getTarget($target);

            // 应用信息
            $agent = $this->workAgentService->getWorkAgentById($agentId, ['id', 'corp_id', 'wx_agent_id', 'wx_secret']);
            if (empty($agent)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '应用不存在');
            }

            // 企业信息
            $corp = $this->corpService->getCorpById($agent['corpId'], ['id', 'wx_corpid', 'employee_secret']);
            if (empty($corp)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '应用对应的企业不存在');
            }

            // 有code值 是授权回调
            if (! empty($code)) {
                return urldecode($this->authCallback($corp, $agent, $code, $target));
            }

            // 跳转至授权
            $redirectUri = Url::getApiBaseUrl() . '/sidebar/agent/auth?agentId=' . $agentId . '&target=' . $target;
            return $this->getOAuthRedirectUrl($corp, $agent, $redirectUri);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s[%s] in %s', $e->getMessage(), $e->getLine(), $e->getFile()));
            $this->logger->error($e->getTraceAsString());
            return urldecode($this->getRedirectUrl($agentId, $e->getCode(), $e->getMessage(), [], $target));
        }
    }

    private function getTarget($target): string
    {
        if (strpos($target, 'http') === false) {
            $target = Url::getSidebarBaseUrl() . $target;
        }
        return urlencode($target);
    }

    private function authCallback(array $corp, array $agent, string $code, string $target)
    {
        $tokenData = $this->getTokenByCode($corp, $agent, $code);
        return $this->getRedirectUrl((int) $agent['id'], 200, '', $tokenData, $target);
    }

    private function getRedirectUrl(int $agentId, int $code, string $message, array $data, string $target)
    {
        $redirectUrl = Url::getSidebarBaseUrl() . '/auth?';
        $query = [
            'agentId' => $agentId,
            'state' => base64_encode(json_encode(responseDataFormat($code, $message, $data))),
            'target' => $target,
        ];
        return $redirectUrl . http_build_query($query);
    }

    private function getOAuthRedirectUrl(array $corp, array $agent, string $redirectUri): string
    {
        $weWorkApp = $this->getWeWorkApp($corp, $agent);
        return $weWorkApp->oauth->redirect($redirectUri)->getTargetUrl();
    }

    private function getWeWorkApp(array $corp, array $agent): Application
    {
        return $this->weWorkClient->app([
            'corp_id' => $corp['wxCorpid'],
            'secret' => $agent['wxSecret'],
            'agent_id' => $agent['wxAgentId'],
        ]);
    }

    /**
     * 获取TOKEN.
     * @param int $corpId
     * @return array ...
     */
    private function getTokenByCode(array $corp, array $agent, string $code): array
    {
        $weWorkApp = $this->getWeWorkApp($corp, $agent);
        $wxData = $weWorkApp->oauth->detailed()->userFromCode($code)->getRaw();
        $this->logger->info('stone::OAuth::getToken::' . json_encode($wxData));
        switch ($wxData['errcode']) {
            case 0:
                break;
            case 50001:
                throw new CommonException(ErrorCode::SERVER_ERROR, '应用的可信域名错误，请检查微信后台配置');
            case 40029:
                throw new CommonException(ErrorCode::TOKEN_INVALID, '授权code码失效');
            default:
                throw new CommonException(ErrorCode::SERVER_ERROR, '获取token失败, ' . $wxData['errmsg']);
        }

        // 员工信息
        $employee = $this->workEmployeeService->getWorkEmployeeAuthByWxUserIdCorpId($wxData['userid'], (int) $corp['id']);

        if (empty($employee)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此员工暂未同步,请联系管理员!');
        }

        $guard = $this->authManager->guard('sidebar');
        /** @var JWTManager $jwt */
        $jwt = $guard->getJwtManager();

        return [
            'token' => $guard->login($employee),
            'expire' => (int) $jwt->getTtl(),
        ];
    }
}
