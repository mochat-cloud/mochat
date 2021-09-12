<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\Action\Sidebar;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;

/**
 * @Controller
 */
class JssdkConfig extends AbstractAction
{
    /**
     * @Inject
     * @var WeWork
     */
    protected $weWorkClient;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    protected $workAgentService;

    /**
     * @var string[] 需要使用的JS接口列表
     */
    protected $wxJsApiList = [
        'getCurExternalContact', // 获取当前外部联系人userid
        'sendChatMessage', // 聊天工具栏分享消息到会话
        'getContext', // 获取进入H5页面的入口环境
        'shareAppMessage', // 自定义转发到会话
        'navigateToAddCustomer', // 进入添加客户界面
    ];

    /**
     * @var \EasyWeChat\Work\Jssdk\Client
     */
    private $jssdk;

    /**
     * @var array wx.config
     */
    private $wxConfig;

    /**
     * @var bool 是否开启BUG
     */
    private $isDebug;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/agent/jssdkConfig", methods="GET")
     */
    public function handle(): array
    {
        $corpId = (int) user()['corpId'];
        $agentId = (int) $this->request->query('agentId', 0);

        if (! $corpId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '企业ID必须');
        }

        ## 企业信息
        $corp = $this->corpService->getCorpById($corpId, ['id', 'wx_corpid', 'employee_secret', 'contact_secret']);
        if (empty($corp)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '应用对应的企业不存在');
        }
        $this->wxConfig = ['corp_id' => $corp['wxCorpid'], 'secret' => $corp['contactSecret']];

        return $agentId ? $this->agentConfig($agentId) : $this->corpConfig();
    }

    protected function setJssdk(): void
    {
        $uriPath = $this->request->query('uriPath', '');
        $this->jssdk = $this->weWorkClient->app($this->wxConfig)->jssdk;
        $this->jssdk->setUrl(urldecode($uriPath));
        $this->isDebug = in_array(env('APP_ENV'), ['dev', 'test']);
    }

    /**
     * 企业配置.
     * @return array ...
     */
    protected function corpConfig(): array
    {
        $this->setJssdk();

        ## 企业config
        $config = $this->jssdk->buildConfig(
            $this->wxJsApiList,
            $this->isDebug,
            true,
            false
        );
        in_array(env('APP_ENV'), ['dev', 'test']) && $config['jsapi_ticket'] = $this->jssdk->getTicket();
        return $config;
    }

    /**
     * 企业应用配置.
     * @param int $agentId 应用ID
     * @return array ...
     */
    protected function agentConfig(int $agentId): array
    {
        ## 应用信息
        $agent = $this->workAgentService->getWorkAgentById($agentId, ['id', 'corp_id', 'wx_agent_id', 'wx_secret']);
        if (empty($agent)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '应用不存在');
        }
        $this->wxConfig['secret'] = $agent['wxSecret'];
        $this->setJssdk();

        $config = $this->jssdk->buildAgentConfig(
            $this->wxJsApiList,
            $agent['wxAgentId'],
            $this->isDebug,
            true,
            false
        );
        in_array(env('APP_ENV'), ['dev', 'test']) && $config['jsapi_ticket'] = $this->jssdk->getAgentTicket($agent['wxAgentId']);

        return $config;
    }
}
