<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Dashboard\Field;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Action\Dashboard\Field\Traits\RequestTrait;
use MoChat\App\WorkContact\Contract\ContactFieldContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 修改 - 页面.
 * @Controller
 */
class StatusUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/statusUpdate", methods="PUT")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['id', 'status'],
            ['status' => 1]
        );

        ## 类型验证
        $this->validated($params, 'statusUpdate');

        ## 业务验证
        $client = $this->container->get(ContactFieldContract::class);
        $data = $client->getContactFieldById($params['id'], ['id']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '数据不存在');
        }

        ## 模型操作
        try {
            $client->updateContactFieldById($params['id'], ['status' => $params['status']]);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '状态修改失败');
        }

        return [];
    }
}
