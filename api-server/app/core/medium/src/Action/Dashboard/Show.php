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
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Logic\Traits\UserTrait;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 查询 - 详情.
 * @Controller
 */
class Show extends AbstractAction
{
    use UserTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/medium/show", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->corpId();
        $id = (int) $this->request->query('id', false);
        if (! $id) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '唯一标识ID必须');
        }

        ## 模型查询
        $client = $this->container->get(MediumContract::class);
        $data = $client->getMediumById($id, ['id', 'media_id', 'type', 'content', 'corp_id', 'medium_group_id', 'user_id', 'user_name']);
        if (empty($data)) {
            return [];
        }
        $data['content'] = $client->addFullPath(json_decode($data['content'], true), $data['type']);
        return $data;
    }
}
