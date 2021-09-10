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

use EasyWeChat\Factory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-H5公众号授权
 * Class Wechat.
 * @Controller
 */
class WeChatSdkConfig extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $appId;

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

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @RequestMapping(path="/operation/shopCode/weChatSdkConfig", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $params = $this->request->all();
        $this->validated($params);
        $appId = $this->getAppId((int) $params['corpId'], 3);
        $url = $params['url'];
        ## EasyWeChat
        $config = config('framework.wechat_open_platform');
        ## 实例化
        $openPlatform = Factory::openPlatform($config);
        $openPlatform = rebind_app($openPlatform, $this->request);
        $result = $openPlatform->getAuthorizer($appId);
        if (! empty($result['authorization_info'])) {
            $officialAccount = $openPlatform->officialAccount($appId, $result['authorization_info']['authorizer_refresh_token']);
            $officialAccount = rebind_app($officialAccount, $this->request);
            $ticket = $officialAccount->jssdk->buildConfig(['getLocation'], false, false, true, [], $url);
//            $officialAccount->jssdk->setUrl($url);
            return json_decode($ticket, true, 512, JSON_THROW_ON_ERROR);
        }
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'url' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'url.required' => 'url 必传',
        ];
    }

    protected function getAppId(int $corpId, int $type): string
    {
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($corpId, $type, ['official_account_id']);
        if (! empty($set)) {
            $info = $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['appid', 'authorizer_appid']);
        } else {
            $info = $this->officialAccountService->getOfficialAccountByCorpId($corpId, ['appid', 'authorizer_appid']);
        }

        return $info['authorizerAppid'];
    }
}
