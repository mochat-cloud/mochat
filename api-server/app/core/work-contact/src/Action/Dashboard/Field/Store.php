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
use Overtrue\Pinyin\Pinyin;

/**
 * 添加 - 动作.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/store", methods="POST")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['label', 'type', 'options', 'order', 'status'],
            ['order' => 0, 'status' => 1, 'options' => []]
        );

        ## 验证
        $this->validated($params, 'store');

        ## 数据处理
        $pinyin = new Pinyin();
        $params['name'] = $pinyin->permalink($params['label'], '', PINYIN_ASCII_TONE);
        $params['options'] = json_encode($params['options'], JSON_UNESCAPED_UNICODE);

        ## 入库
        $client = $this->container->get(ContactFieldContract::class);
        $res = $client->createContactField($params);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '添加失败');
        }

        return [];
    }
}
