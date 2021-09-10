<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactTransfer\Logic\RoomLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Room extends AbstractAction
{
    /**
     * @var RoomLogic
     */
    protected $roomLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(RoomLogic $roomLogic, RequestInterface $request)
    {
        $this->roomLogic = $roomLogic;
        $this->request = $request;
    }

    /**
     * 分配离职在职接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactTransfer/room", methods="GET")
     */
    public function handle(): array
    {
        $params['roomName'] = $this->request->input('roomName');    //群名称

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        return $this->roomLogic->getRoomList($params);
    }
}
