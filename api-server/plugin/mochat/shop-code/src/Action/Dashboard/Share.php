<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\Url;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-分享
 * Class Share.
 * @Controller
 */
class Share extends AbstractAction
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
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/shopCode/share", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        switch ((int) $params['type']) {
            case 1: // 扫码添加店主
                $moduleType = 3;
                break;
            case 2: // 扫码加入门店群
                $moduleType = 4;
                break;
            case 3: // 扫码加入城市群
                $moduleType = 5;
                break;
            default:
                $moduleType = 3;
                break;
        }
        $link = Url::getAuthRedirectUrl($moduleType, $user['corpIds'][0]);
        return ['link' => $link];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => 'type 必传',
        ];
    }
}
