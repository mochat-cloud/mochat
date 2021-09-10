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
use MoChat\Plugin\ContactSop\Logic\InfoLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Info extends AbstractAction
{
    /**
     * @var InfoLogic
     */
    protected $infoLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(InfoLogic $infoLogic, RequestInterface $request)
    {
        $this->infoLogic = $infoLogic;
        $this->request = $request;
    }

    /**
     * 规则详情.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/info", methods="GET")
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id'); //规则id

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        return $this->infoLogic->handle($params);
    }
}
