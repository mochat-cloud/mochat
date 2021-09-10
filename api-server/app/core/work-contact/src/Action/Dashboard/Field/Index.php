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
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/index", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['status', 'page', 'perPage'],
            ['status' => 2, 'page' => 1, 'perPage' => 10]
        );

        ## 契约模型
        $client = $this->container->get(ContactFieldContract::class);
        $where = $params['status'] == 2 ? [] : ['status' => $params['status']];
        $options = [
            'page' => $params['page'],
            'perPage' => $params['perPage'],
            'orderByRaw' => '`order` DESC',
        ];
        $pageData = $client->getContactFieldList(
            $where,
            ['id', 'name', 'label', 'type', 'options', 'status', 'order', 'is_sys'],
            $options
        );

        ## 响应参数处理
        $data = array_map(static function ($item) {
            $item['typeText'] = Options::getMessage($item['type']);
            return $item;
        }, $pageData['data']);

        return [
            'page' => [
                'perPage' => $pageData['per_page'],
                'total' => $pageData['total'],
                'totalPage' => $pageData['last_page'],
            ],
            'list' => $data,
        ];
    }
}
