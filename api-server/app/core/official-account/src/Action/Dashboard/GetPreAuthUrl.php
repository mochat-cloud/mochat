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
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\Codec\Json;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 公众号-构建PC端授权链接
 * Class Index.
 * @Controller
 */
class GetPreAuthUrl extends AbstractAction
{
    use ValidateSceneTrait;

    public const API_START_PUSH_TICKET = 'https://api.weixin.qq.com/cgi-bin/component/api_start_push_ticket';

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @Inject
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/officialAccount/getPreAuthUrl", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $this->config = config('framework.wechat_open_platform');

        if (empty($this->config['app_id'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请检查开放平台参数是否正确配置!');
        }
        ## EasyWeChat
        $app = Factory::openPlatform($this->config);
        $app = rebind_app($app, $this->request);
        ## 获取用户授权页 URL
        $retry = 0;
        getPreAuthorizationUrl:
        try {
            $authUrl = $app->getPreAuthorizationUrl(Url::getDashboardBaseUrl() . "/authRedirect?corp_id={$user['corpIds'][0]}");
            return ['url' => $authUrl];
        } catch (RuntimeException $exception) {
            if (strpos($exception->getMessage(), 'component_verify_ticket') !== false) {
                if ($retry < 3 && $this->startPushTicket()) {
                    ++$retry;
                    sleep(2);
                    goto getPreAuthorizationUrl;
                }
            }

            throw new CommonException(ErrorCode::INVALID_PARAMS, 'component_verify_ticket 不存在，请联系管理员处理！');
        }
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
        ];
    }

    /**
     * 启动推送 ticket.
     */
    protected function startPushTicket()
    {
        $options = [];
        $client = $this->clientFactory->create($options);
        $response = $client->post(self::API_START_PUSH_TICKET, ['json' => [
            'component_appid' => $this->config['app_id'],
            'component_secret' => $this->config['secret'],
        ]]);
        if ($response->getStatusCode() === 200) {
            $content = Json::decode($response->getBody()->getContents());
            dump($content);
            return $content['errcode'] == 0;
        }

        return false;
    }
}
