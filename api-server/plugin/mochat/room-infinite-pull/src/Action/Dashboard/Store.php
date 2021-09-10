<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomInfinitePull\Action\Dashboard;

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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomInfinitePull\Contract\RoomInfiniteContract;
use Psr\Container\ContainerInterface;

/**
 * 无限拉群-新增
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomInfiniteContract
     */
    protected $roomInfiniteService;

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
     * @RequestMapping(path="/dashboard/roomInfinitePull/store", methods="post")
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
        ## 创建标签
        return $this->createRoomRemind($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'name' => 'required',
            'qw_code' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'name.required' => '名称 必传',
            'qw_code.required' => '企微活码 必传',
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
        foreach ($params['qw_code'] as $k => $v) {
            $params['qw_code'][$k]['qrcode'] = File::uploadBase64Image($v['qrcode'], 'image/roomInfinite/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $params['qw_code'][$k]['status'] = 0;
        }
        ## 基本信息
        return [
            'name' => $params['name'],
            'avatar' => isset($params['avatar']) ? File::uploadBase64Image($params['avatar'], 'image/roomInfinite/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg') : '',
            'title_status' => isset($params['title_status']) ? (int) $params['title_status'] : 0,
            'title' => isset($params['title']) ? $params['title'] : '',
            'describe_status' => isset($params['describe_status']) ? (int) $params['describe_status'] : 0,
            'describe' => isset($params['describe']) ? $params['describe'] : '',
            'logo' => isset($params['logo']) ? File::uploadBase64Image($params['logo'], 'image/roomInfinite/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg') : '',
            'qw_code' => json_encode($params['qw_code'], JSON_THROW_ON_ERROR),
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
    private function createRoomRemind(array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->roomInfiniteService->createRoomInfinite($params);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '无限拉群创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
