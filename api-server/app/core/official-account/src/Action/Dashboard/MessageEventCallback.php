<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\OfficialAccount\Action\Dashboard;

use EasyWeChat\Factory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\Framework\Action\AbstractAction;
use Psr\Container\ContainerInterface;

/**
 * 授权变更通知推送
 * @Controller
 */
class MessageEventCallback extends AbstractAction
{
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
     * @var array
     */
    protected $config;

    /**
     * @RequestMapping(path="/dashboard/{appId}/officialAccount/messageEventCallback", methods="get,post")
     */
    public function handle()
    {
        $appId = $this->request->route('appId');
        $config = config('framework.wechat_open_platform');
        ## EasyWeChat第三方平台推送事件
        $openPlatform = Factory::openPlatform($config);
        $openPlatform = rebind_app($openPlatform, $this->request);
        $officialAccount = $openPlatform->officialAccount($appId);
        $officialAccount = rebind_app($officialAccount, $this->request);
        $server = $officialAccount->server;
        $server->push(function () {
            // 暂不处理
//            return 'Welcome!';
        });

        $response = $server->serve();
        return $response->getContent();
    }
}
