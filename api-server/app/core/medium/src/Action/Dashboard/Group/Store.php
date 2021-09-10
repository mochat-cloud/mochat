<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Dashboard\Group;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Medium\Contract\MediumGroupContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Logic\Traits\UserTrait;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 添加 - 动作.
 * @Controller
 */
class Store extends AbstractAction
{
    use UserTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/mediumGroup/store", methods="POST")
     */
    public function handle(): array
    {
        ## 数据验证
        $corpId = $this->corpId();
        $name = $this->request->post('name', false);
        if (! $name) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请输入分组名称');
        }
        $client = $this->container->get(MediumGroupContract::class);
        $existData = $client->getMediumGroupByName($name, ['id']);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '分组名称已存在');
        }

        $insertId = $client->createMediumGroup(['name' => $name, 'corp_id' => $corpId]);
        if (! $insertId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '添加失败');
        }

        return ['id' => $insertId];
    }
}
