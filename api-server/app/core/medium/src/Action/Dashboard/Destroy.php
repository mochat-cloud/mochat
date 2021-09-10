<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Dashboard;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Medium\Action\Dashboard\Traits\RequestTrait;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 删除 - 动作.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/medium/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        $id = $this->request->post('id');
        $this->validated(['id' => $id], 'destroy');

        $client = $this->container->get(MediumContract::class);
        $res = $client->deleteMedium($id);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
        }

        return [];
    }
}
