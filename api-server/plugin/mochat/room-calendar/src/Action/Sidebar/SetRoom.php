<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Action\Sidebar;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

/**
 * 企业微信-侧边栏-群日历设置群聊.
 * @Controller
 */
class SetRoom extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var RoomCalendarPushContract
     */
    protected $roomCalendarPushService;

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
     * StoreLogic constructor.
     */
    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/roomCalendar/setRoom", methods="get")
     * @throws \JsonException
     */
    public function handle()
    {
        $params['corpId'] = (int) $this->request->input('corpId');  //企业 id
        $params['roomId'] = $this->request->input('roomId');       //群聊 id
        $params['id'] = (int) $this->request->input('id');          //活动 id
        ## 参数验证
        $this->validated($params);
        ## 清除原有群日历
        $calendar = $this->roomCalendarService->getRoomCalendarByCorpIdRoomId($params['corpId'], $params['roomId'], ['id', 'rooms']);
        foreach ($calendar as $item) {
            $rooms = json_decode($item['rooms'], true, 512, JSON_THROW_ON_ERROR);
            if (! empty($rooms)) {
                foreach ($rooms as $k => $v) {
                    if ($v['wxChatId'] === $params['roomId']) {
                        unset($rooms[$k]);
                    }
                }
                $rooms = array_merge($rooms);
                $this->roomCalendarService->updateRoomCalendarById($item['id'], ['rooms' => json_encode($rooms, JSON_THROW_ON_ERROR)]);
            }
        }
        ## 群日历详情
        $info = $this->roomCalendarService->getRoomCalendarById($params['id'], ['id', 'rooms']);
        $rooms = empty($info['rooms']) ? [] : json_decode($info['rooms'], true, 512, JSON_THROW_ON_ERROR);
        if (! empty($rooms)) {
            $roomIds = array_column($rooms, 'wxChatId');
            if (in_array($params['roomId'], $roomIds, true)) {
                return [];
            }
        }
        ## 群聊信息
        $room = $this->workRoomService->getWorkRoomByCorpIdWxChatId($params['corpId'], $params['roomId'], ['id', 'wx_chat_id', 'name', 'owner_id', 'room_max']);
        $rooms[count($rooms)] = $room;
        ## 设置群聊
        $this->roomCalendarService->updateRoomCalendarById($params['id'], ['rooms' => json_encode($rooms, JSON_THROW_ON_ERROR)]);
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId' => 'required',
            'roomId' => 'required',
            'id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => 'corpId 必传',
            'roomId.required' => 'roomId 必传',
            'id.required' => 'id 必传',
        ];
    }
}
