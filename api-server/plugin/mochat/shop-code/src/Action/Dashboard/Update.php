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
 * 门店活码-修改
 * Class Update.
 * @Controller
 */
class Update extends AbstractAction
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
     * @RequestMapping(path="/dashboard/shopCode/update", methods="put")
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
        $this->validated($params, 'update');
        ## 处理参数
        $data = $this->handleParam($params);
        ## 处理重复
        if (! empty($data['address'])) {
            $info = $this->shopCodeService->getShopCodeByNameAddress($user['corpIds'][0], $data['address'], (int) $params['id'], ['id']);
            if (! empty($info)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '地址重复，不可操作');
            }
        }

        ## 创建标签
        return $this->updateShopCode((int) $params['id'], $user, $data);
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
            'id.required' => 'id 必传',
            'type.required' => 'type 必传',
            'status.required' => '开启状态 必传',
        ];
    }

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleParam(array $params): array
    {
        ## 基本信息
        return [
            'name' => isset($params['name']) ? $params['name'] : '',
            'type' => $params['type'],
            'employee' => (isset($params['employee']) && ! empty($params['employee'])) ? json_encode($params['employee'], JSON_THROW_ON_ERROR) : '{}',
            'qw_code' => isset($params['qwCode']) ? json_encode($params['qwCode'], JSON_THROW_ON_ERROR) : '{}',
            'search_keyword' => isset($params['searchKeyword']) ? $params['searchKeyword'] : '',
            'address' => isset($params['address']) ? $params['address'] : '',
            'country' => isset($params['country']) ? $params['country'] : '',
            'province' => isset($params['province']) ? $params['province'] : '',
            'city' => isset($params['city']) ? $params['city'] : '',
            'district' => isset($params['district']) ? $params['district'] : '',
            'lat' => isset($params['lat']) ? $params['lat'] : '',
            'lng' => isset($params['lng']) ? $params['lng'] : '',
            'status' => $params['status'],
        ];
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function updateShopCode(int $id, array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $this->shopCodeService->updateShopCodeById($id, $params);
            if ((int) $params['type'] === 1) {
                $employee = json_decode($params['employee'], true, 512, JSON_THROW_ON_ERROR);
                $this->handleQrcode($user, $employee['wxUserId'], $id);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '门店活码修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
