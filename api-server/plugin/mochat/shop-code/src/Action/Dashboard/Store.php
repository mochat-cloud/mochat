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
 * 门店活码-新增
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
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
     * @RequestMapping(path="/dashboard/shopCode/store", methods="post")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params, 'store');
        ## 处理参数
        $params = $this->handleParam($user, $params);
        ## 处理重复
        if (! empty($params['address'])) {
            $info = $this->shopCodeService->getShopCodeByNameAddress($user['corpIds'][0], $params['address'], 0, ['id']);
            if (! empty($info)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '地址重复，不可操作');
            }
        }
        ## 创建标签
        return $this->createShopCode($user, $params);
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
            'status' => 'required',
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
            'status.required' => '开启状态 必传',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ## 基本信息
        return [
            'name' => isset($params['name']) ? $params['name'] : '',
            'type' => $params['type'],
            'employee' => (isset($params['employee']) && ! empty($params['employee'])) ? json_encode($params['employee'], JSON_THROW_ON_ERROR) : '{}',
            'qw_code' => isset($params['qwCode']) ? json_encode($params['qwCode'], JSON_THROW_ON_ERROR) : '{}',
            'address' => isset($params['address']) ? $params['address'] : '',
            'search_keyword' => isset($params['searchKeyword']) ? $params['searchKeyword'] : '',
            'country' => isset($params['country']) ? $params['country'] : '',
            'province' => isset($params['province']) ? $params['province'] : '',
            'city' => isset($params['city']) ? $params['city'] : '',
            'district' => isset($params['district']) ? $params['district'] : '',
            'lat' => isset($params['lat']) ? $params['lat'] : '',
            'lng' => isset($params['lng']) ? $params['lng'] : '',
            'status' => $params['status'],
            'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
            'corp_id' => $user['corpIds'][0],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function createShopCode(array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->shopCodeService->createShopCode($params);
            if ((int) $params['type'] === 1) {
                $employee = json_decode($params['employee'], true, 512, JSON_THROW_ON_ERROR);
                $this->handleQrcode($user, $employee['wxUserId'], $id);
            }
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
