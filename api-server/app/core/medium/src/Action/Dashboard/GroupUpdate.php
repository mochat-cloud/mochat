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
 * 分组修改 - 动作.
 * @Controller
 */
class GroupUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/medium/groupUpdate", methods="PUT")
     */
    public function handle(): array
    {
        $id = $this->request->input('id');
        $groupId = $this->request->input('mediumGroupId');

        $this->validated(['id' => $id, 'mediumGroupId' => $groupId], 'groupUpdate');

        $client = $this->container->get(MediumContract::class);
        $res = $client->updateMediumById($id, ['mediumGroupId' => $groupId]);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '移动失败');
        }

        return [];
    }
}
