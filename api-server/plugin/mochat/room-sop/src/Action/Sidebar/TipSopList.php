<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Action\Sidebar;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\RoomSop\Logic\TipSopListLogic;

/**
 * h5侧边栏接口.
 * @Controller
 */
class TipSopList extends AbstractAction
{
    /**
     * @Inject
     * @var TipSopListLogic
     */
    protected $tipSopList;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class),
     * })
     * @RequestMapping(path="/sidebar/roomSop/tipSopList", methods="GET")
     */
    public function handle(): array
    {
        $user = user();
        $params['corpId'] = (int) $user['corpId']; //企业id

        return $this->tipSopList->handle($params);
    }
}
