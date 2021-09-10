<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactSop\Logic\IndexLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(IndexLogic $indexLogic, RequestInterface $request)
    {
        $this->indexLogic = $indexLogic;
        $this->request = $request;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/index", methods="GET")
     */
    public function handle(): array
    {
        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        return $this->indexLogic->handle($params);
    }
}
