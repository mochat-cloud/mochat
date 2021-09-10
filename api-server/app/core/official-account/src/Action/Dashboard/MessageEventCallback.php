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
use EasyWeChat\Kernel\Messages\Text;
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

    private $caseIds = [
        'gh_3c884a361561',
        'gh_c0f28a78b318',
        'gh_3f222ed8d140',
        'gh_26128078e9ab',
        'gh_2b3713f184a6',
        'gh_8dad206e9538',
        'gh_905ae9d01059',
        'gh_393666f1fdf4',
        'gh_39abb5d4e1b7',
        'gh_7818dcb60240',
    ];

    public function __call($name, $arguments)
    {
        return $this->handleOther(...$arguments);
    }

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
        $server->push(function ($message) use ($openPlatform) {
            $this->logger->debug(sprintf('微信事件Message::[%s]', json_encode($message, JSON_THROW_ON_ERROR)));
            if (in_array($message['ToUserName'], $this->caseIds) && $message['MsgType'] == 'text') {
                return $this->handleCase($message, $openPlatform);
            }
            $func = 'handle' . ucfirst($message['MsgType']);
            return $this->{$func}($message);
        });
        $response = $server->serve();
        $this->logger->debug(sprintf('微信事件return::[%s]', $response->getContent()));
        return $response->getContent();
    }

    private function handleText(array $message)
    {
        return 'Hello！';
    }

    private function handleCase(array $message, $openPlatform)
    {
        if ($message['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
            return $message['Content'] . '_callback';
        }
        if (substr($message['Content'], 0, 15) == 'QUERY_AUTH_CODE') {
            $queryAuthCode = substr($message['Content'], 16);

//            $config = config('framework.wechat_open_platform');
//            $openPlatform = Factory::openPlatform($config);
//            $openPlatform = rebind_app($openPlatform, $this->request);
            $authInfo = $openPlatform->handleAuthorize($queryAuthCode);
            $this->logger->debug(sprintf('微信事件$authInfo::[%s]', json_encode($authInfo, JSON_THROW_ON_ERROR)));
            try {
                $this->logger->debug(sprintf('微信事件authorizer_appid::[%s]', $authInfo['authorization_info']['authorizer_appid']));
                $this->logger->debug(sprintf('微信事件authorizer_refresh_token::[%s]', $authInfo['authorization_info']['authorizer_refresh_token']));
                $officialAccount = $openPlatform->officialAccount($authInfo['authorization_info']['authorizer_appid'], $authInfo['authorization_info']['authorizer_refresh_token']);
                $officialAccount = rebind_app($officialAccount, $this->request);
                $res = $officialAccount->customer_service
                    ->message(new Text($queryAuthCode . '_from_api'))
                    ->to($message['FromUserName'])
                    ->send();
                $this->logger->debug(sprintf('微信事件customer_service::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            }
        }
        return '';
    }

    private function handleOther(array $message)
    {
        return '';
    }
}
