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
use MoChat\Plugin\RoomSop\Logic\GetSopInfoLogic;

/**
 * h5弹窗接口.
 * @Controller
 */
class GetSopInfo extends AbstractAction
{
    /**
     * @Inject
     * @var GetSopInfoLogic
     */
    protected $getSopInfo;

    /**
     * 规则详情.
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/roomSop/getSopInfo", methods="GET")
     */
    public function handle(): array
    {
        $params['id'] = (int) $this->request->input('id');        //规则log id

        return $this->getSopInfo->handle($params);
    }
}
