<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ChannelCode\Logic\IndexLogic;

/**
 * 渠道码列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/channelCode/index", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params = $this->request->inputs(
            [
                'name', 'type', 'groupId', 'page', 'perPage',
            ],
            [
                'page' => 1, 'perPage' => 20,
            ]
        );

        return $this->indexLogic->handle($params);
    }
}
