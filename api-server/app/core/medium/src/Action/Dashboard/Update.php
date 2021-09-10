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
 * 修改 - 页面.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/medium/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['id', 'type', 'isSync', 'content', 'mediumGroupId'],
            ['isSync' => 1]
        );

        ## 验证
        $this->validated($params, 'update');

        ## 数据整理
        $userInfo = user();
        $params = array_merge($params, [
            'user_id' => $userInfo['id'],
            'user_name' => $userInfo['name'],
            'content' => json_encode($params['content'], JSON_UNESCAPED_UNICODE),
        ]);

        ## 修改
        $id = $params['id'];
        unset($params['id']);
        try {
            $this->container->get(MediumContract::class)->updateMediumById($id, $params);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
        }
        return [];
    }
}
