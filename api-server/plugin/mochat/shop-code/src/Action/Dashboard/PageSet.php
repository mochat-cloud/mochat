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
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\File;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodePageContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-页面设置
 * Class Share.
 * @Controller
 */
class PageSet extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodePageContract
     */
    protected $shopCodePageService;

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
     * @RequestMapping(path="/dashboard/shopCode/pageSet", methods="post")
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
        $this->validated($params);
        ## 处理参数
//        return [$params['default_style']['guide']];
        $data = $this->handleParam($user, $params);
        ## 创建标签
        $id = (isset($params['id']) && (int) $params['id'] > 0) ? (int) $params['id'] : 0;
        return $this->createShopCodePage($user, $data, $id);
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
            'title' => 'required',
            'show_type' => 'required',
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
            'title.required' => '页面标题 必传',
            'show_type.required' => '扫码页面展示 必传',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException|\League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        if ((int) $params['show_type'] === 1) {
            $params['default']['logo'] = File::uploadBase64Image($params['default']['logo'], 'image/shopCode/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }
        ## 基本信息
        if (isset($params['id']) && (int) $params['id'] > 0) {
            ## 修改
            return [
                'type' => (int) $params['type'],
                'title' => $params['title'],
                'show_type' => (int) $params['show_type'],
                'default' => (isset($params['default']) && ! empty($params['default'])) ? json_encode($params['default'], JSON_THROW_ON_ERROR) : '{}',
                'poster' => (isset($params['poster']) && ! empty($params['poster'])) ? File::uploadBase64Image($params['poster'], 'image/shopCode/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg') : '',
                'autoPass' => (isset($params['autoPass']) && is_numeric($params['autoPass'])) ? $params['autoPass'] : 0,
            ];
        }

        ## 新增
        return [
            'type' => $params['type'],
            'title' => $params['title'],
            'show_type' => $params['show_type'],
            'default' => (isset($params['default']) && ! empty($params['default'])) ? json_encode($params['default'], JSON_THROW_ON_ERROR) : '{}',
            'poster' => (isset($params['poster']) && ! empty($params['poster'])) ? $params['poster'] : '',
            'autoPass' => (isset($params['autoPass']) && is_numeric($params['autoPass'])) ? $params['autoPass'] : 0,
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
    private function createShopCodePage(array $user, array $params, int $id): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $info = $this->shopCodePageService->getShopCodePageByCorpIdType($user['corpIds'][0], $params['type']);
            if (! empty($info)) {
                ## 更新
                $id = $this->shopCodePageService->updateShopCodePageById($id, $params);
            } else {
                ## 新增
                $id = $this->shopCodePageService->createShopCodePage($params);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '门店活码页面设置失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
