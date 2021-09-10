<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;

/**
 * 群日历 - 添加群聊.
 *
 * Class AddRoom.
 * @Controller
 */
class AddRoom extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomCalendar/addRoom", methods="post")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params, 'store');

        return $this->updateRoomCalendar($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '群日历ID 必填',
            'id.integer' => '群日历ID 必需为整数',
            'id.min  ' => '群日历ID 不可小于1',
        ];
    }

    private function updateRoomCalendar(array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 群日历详情
            $info = $this->roomCalendarService->getRoomCalendarById((int) $params['id'], ['id', 'rooms']);
            $infoRooms = empty($info['rooms']) ? [] : json_decode($info['rooms'], true, 512, JSON_THROW_ON_ERROR);

            foreach ($params['rooms'] as $room) {
                ## 清除原有群日历
                $calendar = $this->roomCalendarService->getRoomCalendarByCorpIdRoomId($user['corpIds'][0], $room['wxChatId'], ['id', 'rooms']);
                foreach ($calendar as $item) {
                    if ($item['id'] === (int) $params['id']) {
                        continue;
                    }
                    $rooms = json_decode($item['rooms'], true, 512, JSON_THROW_ON_ERROR);
                    if (! empty($rooms)) {
                        foreach ($rooms as $k => $v) {
                            if ($v['wxChatId'] === $room['wxChatId']) {
                                unset($rooms[$k]);
                            }
                        }
                        $rooms = array_merge($rooms);
                        $this->roomCalendarService->updateRoomCalendarById($item['id'], ['rooms' => json_encode($rooms, JSON_THROW_ON_ERROR)]);
                    }
                }
                ## 处理群聊
                if (! empty($infoRooms)) {
                    $roomIds = array_column($infoRooms, 'wxChatId');
                    if (in_array($room['wxChatId'], $roomIds, true)) {
                        continue;
                    }
                }
                $infoRooms[] = $room;
            }

            ## 创建活动
            $id = $this->roomCalendarService->updateRoomCalendarById((int) $params['id'], ['rooms' => json_encode($infoRooms, JSON_THROW_ON_ERROR)]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '群日历添加群聊失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
