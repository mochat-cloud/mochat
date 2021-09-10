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
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomInfinitePull\Contract\RoomInfiniteContract;

/**
 * 无限拉群-删除.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomInfiniteContract
     */
    protected $roomInfiniteService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomInfinitePull/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $this->validated($this->request->all(), 'destroy');
        $id = (int) $this->request->input('id');
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 数据操作
        Db::beginTransaction();
        try {
            $this->roomInfiniteService->deleteRoomInfinite($id);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $loggers = $this->container->get(StdoutLoggerInterface::class);
            $loggers->error(sprintf('%s [%s] %s', '无限拉群删除失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $loggers->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '无限拉群删除失败' . $e->getMessage()); //$e->getMessage()
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
            'id' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动id 必填',
            'id.integer' => '活动id 必须为整型',
        ];
    }
}
