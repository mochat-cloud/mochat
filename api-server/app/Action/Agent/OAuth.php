<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Agent;

use App\Constants\User\Status;
use App\Contract\CorpServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkAgentServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use EasyWeChat\Work\Application;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\SimpleJwt\JWTManager;

/**
 * @Controller
 */
class OAuth extends AbstractAction
{
    /**
     * @Inject
     * @var WeWork
     */
    protected $weWorkClient;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject
     * @var WorkAgentServiceInterface
     */
    protected $workAgentClient;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpClient;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/agent/oauth", methods="GET")
     */
    public function handle()
    {
        $this->logger = $this->container->get(LoggerFactory::class)->get('wework-h5-oauth');

        $agentId = (int) $this->request->query('agentId', 0);
        if (! $agentId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '应用ID必须');
        }

        ## 应用信息
        $agent = $this->workAgentClient->getWorkAgentById($agentId, ['id', 'corp_id', 'wx_agent_id', 'wx_secret']);
        if (empty($agent)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '应用不存在');
        }

        ## 企业信息
        $corp = $this->corpClient->getCorpById($agent['corpId'], ['id', 'wx_corpid', 'employee_secret']);
        if (empty($corp)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '应用对应的企业不存在');
        }

        ## wework实例 - 调用
        if (! $this->request->query('code', false)) {
            $this->app = $this->weWorkClient->app([
                'corp_id'  => $corp['wxCorpid'],
                'secret'   => $agent['wxSecret'],
                'agent_id' => $agent['wxAgentId'],
            ]);
            return $this->buildAuthUrl($agentId);
        }
        $isJsRedirect = $this->request->query('isJsRedirect', 0);
        $redirectUrl  = config('framework.js_domain') . '/codeAuth?callValues=';
        $queryParam   = function (array $data, int $code = 200, string $msg = '') use ($corp): string {
            return base64_encode(json_encode(responseDataFormat($code, $msg, array_merge(
                $data,
                ['corpId' => $corp['id']],
                $this->request->getQueryParams()
            ))));
        };
        try {
            $this->app = $this->weWorkClient->app([
                'corp_id'  => $corp['wxCorpid'],
                'secret'   => $agent['wxSecret'],
                'agent_id' => $agent['wxAgentId'],
            ]);
            $tokenData = $this->getToken();
            if ($isJsRedirect) {
                $redirectUrl .= $queryParam($tokenData);
                return $this->response->redirect($redirectUrl);
            }
            return $tokenData;
        } catch (\Exception $ex) {
            if ($isJsRedirect) {
                $redirectUrl .= $queryParam([], $ex->getCode(), $ex->getMessage());
                return $this->response->redirect($redirectUrl);
            }
            throw $ex;
        }
    }

    /**
     * 构造授权.
     * @param int $agentId 应用ID
     * @return array ...
     */
    protected function buildAuthUrl(int $agentId): array
    {
        $isJsRedirect = $this->request->query('isJsRedirect', 0);
        $act          = $this->request->query('act', '');
        $callbackUrl  = config('framework.app_domain') . '/agent/oauth';
        $callbackUrl .= '?' . http_build_query(['act' => $act, 'agentId' => $agentId, 'isJsRedirect' => $isJsRedirect]);
        // 返回一个 redirect 实例
        $redirect = $this->app->oauth->redirect($callbackUrl);
        // 获取企业微信跳转目标地址
        $targetUrl = $redirect->getTargetUrl();

        return ['url' => $targetUrl];
    }

    /**
     * 获取TOKEN.
     * @return array ...
     */
    protected function getToken(): array
    {
        $wxData = $this->app->oauth->detailed()->user()->getOriginal();
        $this->logger->info('stone::OAuth::getToken::' . json_encode($wxData));
        switch ($wxData['errcode']) {
            case 0:
                break;
            case 50001:
                throw new CommonException(ErrorCode::SERVER_ERROR, '应用的可信域名错误，请检查微信后台配置');
            case 40029:
                throw new CommonException(ErrorCode::TOKEN_INVALID, '授权code码失效');
            default:
                throw new CommonException(ErrorCode::SERVER_ERROR, '获取token失败');
        }

        ## 员工信息
        $employee = $this->container->get(WorkEmployeeServiceInterface::class)->getWorkEmployeeByWxUserId($wxData['UserId'], [
            'id', 'log_user_id',
        ]);
        if (empty($employee)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此员工未同步');
        }

        ## 子账户信息
        $user = $this->container->get(UserServiceInterface::class)->getUserModelById($employee['logUserId'], ['id', 'status']);
        if (empty($user)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此员工未关联子账户');
        }
        if ((int) $user['status'] !== Status::NORMAL) {
            throw new CommonException(ErrorCode::ACCESS_REFUSE, sprintf('账户%s，无法登录', Status::getMessage($user['status'])));
        }

        $guard = $this->container->get(AuthManager::class)->guard('jwt');
        /** @var JWTManager $jwt */
        $jwt = $guard->getJwtManager();

        return [
            'token'  => $guard->login($user),
            'expire' => $jwt->getTtl(),
        ];
    }
}
