<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkRoom\Action\Sidebar;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;
use Psr\Container\ContainerInterface;

/**
 * 企业微信-侧边栏-群管理.
 * @Controller
 */
class RoomManage extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

    /**
     * @Inject
     * @var RoomSopContract
     */
    protected $roomSopService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/workRoom/roomManage", methods="get")
     * @throws \JsonException
     */
    public function handle()
    {
        $params['corpId'] = (int) user()['corpId'];  //企业 id
        $params['roomId'] = $this->request->input('roomId');       //群聊 id
        // 验证接受参数
        $this->validated($params);
        // 群sop
        $room = $this->workRoomService->getWorkRoomByCorpIdWxChatId($params['corpId'], $params['roomId'], ['id']);

        if (empty($room)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '群不存在');
        }

        $roomId = $room['id'];
        $sop = $this->roomSopService->getRoomSopByCorpIdRoomId($params['corpId'], $roomId, ['name']);
        // 群日历
        $calendar = $this->roomCalendarService->getRoomCalendarByCorpIdRoomId($params['corpId'], $params['roomId'], ['name']);
        // 群聊质检
        $quality = $this->roomQualityService->getRoomQualityByCorpIdRoomId($params['corpId'], $params['roomId'], ['name']);
        return ['sop' => $sop, 'calendar' => $calendar, 'quality' => $quality];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'roomId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'roomId.required' => 'roomId 必传',
        ];
    }
}
