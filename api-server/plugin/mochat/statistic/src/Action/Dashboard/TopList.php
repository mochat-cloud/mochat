<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Statistic\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\Statistic\Logic\TopListLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class TopList extends AbstractAction
{
    /**
     * @var TopListLogic
     */
    protected $topListLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(TopListLogic $topListLogic, RequestInterface $request)
    {
        $this->topListLogic = $topListLogic;
        $this->request = $request;
    }

    /**
     * 客户数量前十排行榜.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/statistic/topList", methods="GET")
     */
    public function handle(): array
    {
        return $this->topListLogic->getTopList(10);
    }
}
