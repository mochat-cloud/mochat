<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ChannelCode;

use App\Logic\ChannelCode\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 渠道码列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/channelCode/index", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params = $this->request->inputs(
            [
                'name', 'type', 'groupId', 'page', 'perPage',
            ],
            [
                'page' => 1, 'perPage' => 20,
            ]
        );

        return $this->indexLogic->handle($params);
    }
}
