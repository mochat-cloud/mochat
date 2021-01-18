<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Role;

use App\Logic\Role\PermissionStoreLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 角色赋权.
 * @Controller
 */
class PermissionStore extends AbstractAction
{
    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/permissionStore", methods="post")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $roleId = (int) $this->request->post('roleId', 0);
        if (! $roleId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '角色必须');
        }
        $menuIds = $this->request->post('menuIds', []);

        $res = $this->container->get(PermissionStoreLogic::class)->handle($roleId, $menuIds);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '角色授权失败');
        }

        return [];
    }
}
