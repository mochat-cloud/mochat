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
use MoChat\App\WorkContact\Constants\Field\Options;
use MoChat\App\WorkContact\Contract\ContactFieldContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 详情.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/show", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数
        $id = (int) $this->request->input('id');

        ## 验证
        $this->validated(['id' => $id], 'show');

        ## 契约模型
        $client = $this->container->get(ContactFieldContract::class);
        $data = $client->getContactFieldById($id, ['id', 'name', 'label', 'type', 'options', 'status', 'order', 'is_sys']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '无此条信息');
        }
        $data['typeText'] = Options::getMessage($data['type']);

        return $data;
    }
}
