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
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\RoomSop\Logic\TipSopDelRoomLogic;

/**
 * h5侧边栏接口.
 * @Controller
 */
class TipSopDelRoom extends AbstractAction
{
    /**
     * @Inject
     * @var TipSopDelRoomLogic
     */
    protected $tipSopDelRoom;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/roomSop/tipSopDelRoom", methods="GET")
     */
    public function handle(): array
    {
        $user = user();
        $params['corpId'] = (int) $user['corpId'];
        $params['id'] = (int) $this->request->input('id');               //sop id
        $roomId = $this->request->input('roomId');                      //群聊id
        $params['roomId'] = $this->workRoomService->getWorkRoomByCorpIdWxChatId($params['corpId'], $roomId, ['id'])['id'];       //群聊id

        return $this->tipSopDelRoom->handle($params);
    }
}
