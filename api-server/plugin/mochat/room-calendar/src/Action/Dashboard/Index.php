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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;

/**
 * 群日历-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomCalendar/index", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 验证参数
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'name' => $this->request->input('name'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        $params = $this->handleParams($user, $params);

        return $this->getRoomCalendarList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where['corp_id'] = $user['corpIds'][0];
        if ($user['isSuperAdmin'] === 0) {
            $where['create_user_id'] = $user['id'];
        }
        if (isset($params['name']) && ! empty($params['name'])) {
            $where[] = ['name', 'LIKE', '%' . $params['name'] . '%'];
        }

        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取互动雷达列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomCalendarList(array $params): array
    {
        $columns = ['id', 'name', 'rooms', 'on_off', 'create_user_id', 'created_at'];
        $roomCalendarList = $this->roomCalendarService->getRoomCalendarList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomCalendarList['data']) ? $data : $this->handleData($roomCalendarList);
    }

    /**
     * 数据处理.
     * @param array $roomCalendarList 群日历列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $roomCalendarList): array
    {
        $list = [];
        foreach ($roomCalendarList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'room_ids' => empty($val['rooms']) ? '' : json_decode($val['rooms'], true, 512, JSON_THROW_ON_ERROR),
                'on_off' => $val['onOff'],
                'nickname' => isset($username['name']) ? $username['name'] : '',
                'created_at' => $val['createdAt'],
            ];
        }
        $data['page']['total'] = $roomCalendarList['total'];
        $data['page']['totalPage'] = $roomCalendarList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
