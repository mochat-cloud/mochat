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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

/**
 * 群日历- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

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
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/roomCalendar/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData((int) $id);
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
            'id.required' => '活动ID 必填',
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
        ];
    }

    private function handleData(int $id): array
    {
        ## 数据
        $calendar = $this->roomCalendarService->getRoomCalendarById($id, ['id', 'name', 'rooms']);
        $calendar['rooms'] = empty($calendar['rooms']) ? '' : json_decode($calendar['rooms'], true, 512, JSON_THROW_ON_ERROR);
        $push = $this->roomCalendarPushService->getRoomCalendarPushByRoomCalendarId($id, ['id', 'room_calendar_id', 'name', 'day', 'push_content']);

        foreach ($push as $key => $val) {
            $pushContent = json_decode($val['pushContent'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($pushContent as $k => $v) {
                if ($v['type'] === 'image') {
                    $pushContent[$k]['path'] = $v['pic'];
                    $pushContent[$k]['pic'] = file_full_url($v['pic']);
                }
                if ($v['type'] === 'link') {
                    $pushContent[$k]['path'] = file_full_url($v['link_cover']);
                    $pushContent[$k]['link_cover'] = $v['link_cover'];
                }
            }
            $push[$key]['pushContent'] = $pushContent;
        }

        return [
            'calendar' => $calendar,
            'push' => $push,
        ];
    }
}
