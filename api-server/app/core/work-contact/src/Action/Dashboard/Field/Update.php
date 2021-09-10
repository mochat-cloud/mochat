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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Action\Dashboard\Field\Traits\RequestTrait;
use MoChat\App\WorkContact\Action\Dashboard\Field\Traits\UpdateTrait;
use MoChat\App\WorkContact\Contract\ContactFieldContract;
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
    use UpdateTrait;

    /**
     * @Inject
     * @var ContactFieldContract
     */
    protected $contactFieldService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['id', 'label', 'type', 'options', 'order', 'status'],
            ['options' => [], 'order' => 0]
        );

        ## 类型验证
        $this->validated($params, 'update');

        ## 数据处理
        $params['order'] = (int) $params['order'];
        ## 业务验证
        $data = $this->contactFieldService->getContactFieldById($params['id'], ['id', 'label', 'type', 'options', 'is_sys', 'name']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '数据不存在');
        }
        $dbData = $this->handleUpdateParam($params, $data);

        try {
            $this->contactFieldService->updateContactFieldById($params['id'], $dbData);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
        }

        return [];
    }
}
