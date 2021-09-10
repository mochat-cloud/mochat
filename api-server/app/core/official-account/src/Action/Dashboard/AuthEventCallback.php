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
use EasyWeChat\OpenPlatform\Server\Guard;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\Framework\Action\AbstractAction;
use Psr\Container\ContainerInterface;

/**
 * @Controller
 */
class AuthEventCallback extends AbstractAction
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
     * @var \EasyWeChat\OpenPlatform\Application
     */
    protected $app;

    /**
     * @RequestMapping(path="/dashboard/officialAccount/authEventCallback", methods="get,post")
     */
    public function handle(): string
    {
        $config = config('framework.wechat_open_platform');
        ##EasyWeChat第三方平台推送事件
        $this->app = Factory::openPlatform($config);
        $this->app = rebind_app($this->app, $this->request);

        $this->app->server->push(function ($message) {
            $this->logger->debug(sprintf('微信回调Message::[%s]', json_encode($message, JSON_THROW_ON_ERROR)));

            # 获取授权公众号 AppId： $message['AuthorizerAppid']  获取 AuthCode：$message['AuthorizationCode']
            $data = [];
            if (isset($message['AuthorizationCode'])) {
                $type = $message['InfoType'] === 'authorized' ? 1 : 2;
                if ($message['InfoType'] === 'authorized' || $message['InfoType'] === 'updateauthorized') {
                    $data = [
                        'appid' => $message['AppId'],
                        'authorized_status' => $type,
                        'create_time' => $message['CreateTime'],
                        'authorizer_appid' => $message['AuthorizerAppid'],
                        'authorization_code' => $message['AuthorizationCode'],
                        'pre_auth_code' => $message['PreAuthCode'],
                        'encoding_aes_key' => $this->config['aes_key'],
                        'token' => $this->config['token'],
                        'secret' => $this->config['secret'],
                    ];
                }
            }
            if (! empty($data)) {
                $this->handleData($data);
            }
        }, Guard::EVENT_AUTHORIZED);

        $this->app->server->push(function ($message) {
            $this->logger->debug(sprintf('微信回调Message::[%s]', json_encode($message, JSON_THROW_ON_ERROR)));
            if (isset($message['InfoType']) && $message['InfoType'] === 'unauthorized') {
                $data = [
                    'appid' => $message['AppId'],
                    'authorizer_appid' => $message['AuthorizerAppid'],
                    'authorized_status' => 3,
                    'create_time' => $message['CreateTime'],
                ];
            }
            if (! empty($data)) {
                $this->handleData($data);
            }
        }, Guard::EVENT_UNAUTHORIZED);

        $this->logger->info(sprintf('微信回调::[%s]', '成功'));
        $response = $this->app->server->serve();

        return $response->getContent();
    }

    protected function handleData(array $data)
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            $info = $this->officialAccountService->getOfficialAccountByAppIdAuthorizerAppid($data['appid'], $data['authorizer_appid'], ['id']);
            if (empty($info)) {
                $this->officialAccountService->createOfficialAccount($data);
            } else {
                $this->officialAccountService->updateOfficialAccountById($info['id'], $data);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '授权失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
