<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomRemind\Action\Dashboard;

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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomRemind\Contract\RoomRemindContract;
use Psr\Container\ContainerInterface;

/**
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomRemindContract
     */
    protected $roomRemindService;

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
     * @RequestMapping(path="/dashboard/roomRemind/store", methods="post")
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
            'rooms' => 'required',
            'is_qrcode' => 'required',
            'is_link' => 'required',
            'is_miniprogram' => 'required',
            'is_card' => 'required',
            'is_keyword' => 'required',
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
            'rooms.required' => '群聊 必传',
            'is_qrcode.required' => '发送带二维码图片 必传',
            'is_link.required' => '发送链接分享 必传',
            'is_miniprogram.required' => '发送小程序 必传',
            'is_card.required' => '发送名片 必传',
            'is_keyword.required' => '发送关键词 必传',
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
            'name' => $params['name'],
            'rooms' => json_encode($params['rooms'], JSON_THROW_ON_ERROR),
            'is_qrcode' => (int) $params['is_qrcode'],
            'is_link' => (int) $params['is_link'],
            'is_miniprogram' => (int) $params['is_miniprogram'],
            'is_card' => (int) $params['is_card'],
            'is_keyword' => $params['is_keyword'],
            'keyword' => (isset($params['keyword']) && ! empty($params['keyword'])) ? implode(',', $params['keyword']) : '',
            'status' => 1,
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
            $id = $this->roomRemindService->createRoomRemind($params);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户群提醒创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
