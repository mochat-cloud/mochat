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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactSop\Logic\DestroyLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Destroy extends AbstractAction
{
    /**
     * @var DestroyLogic
     */
    protected $destroyLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(DestroyLogic $destroyLogic, RequestInterface $request)
    {
        $this->destroyLogic = $destroyLogic;
        $this->request = $request;
    }

    /**
     * 编辑规则接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id'); //规则id

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        $res = $this->destroyLogic->handle($params);
        if ($res) {
            return [];
        }
        throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
    }
}
