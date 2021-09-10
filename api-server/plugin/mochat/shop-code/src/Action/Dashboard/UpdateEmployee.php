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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Action\Dashboard\Traits\UpdateTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-修改店主
 * Class UpdateEmployee.
 * @Controller
 */
class UpdateEmployee extends AbstractAction
{
    use ValidateSceneTrait;
    use UpdateTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

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
     * @RequestMapping(path="/dashboard/shopCode/updateEmployee", methods="post")
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
        $this->validated($params, 'store');
        ## 创建标签
        return $this->updateShopCode($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required',
            'employee' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => 'id 必传',
            'employee.required' => '店主 必传',
        ];
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function updateShopCode(array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->shopCodeService->updateShopCodeById((int) $params['id'], ['employee' => json_encode($params['employee'], JSON_THROW_ON_ERROR)]);
            $employee = $params['employee'];
            $this->handleQrcode($user, $employee['wxUserId'], $id);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '门店活码创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
