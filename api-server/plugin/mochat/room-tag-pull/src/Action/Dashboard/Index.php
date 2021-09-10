<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;
use Psr\Container\ContainerInterface;

/**
 * 标签建群-列表.
 *
 * Class Index.
 * @Controller
 */
class Index
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomTagPullContract
     */
    protected $roomTagPullService;

    /**
     * @Inject
     * @var RoomTagPullContactContract
     */
    protected $roomTagPullContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomTagPull/index", methods="get")
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

        return $this->getRoomTagPullList($user, $params);
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
        if (isset($params['name'])) {
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
     * 获取自动打标签列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomTagPullList(array $user, array $params): array
    {
        $columns = ['id', 'name', 'rooms', 'employees', 'wx_tid', 'created_at'];
        $roomTagPullList = $this->roomTagPullService->getRoomTagPullList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomTagPullList['data']) ? $data : $this->handleData($user, $roomTagPullList);
    }

    /**
     * 数据处理.
     * @param array $roomTagPullList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $user, array $roomTagPullList): array
    {
        $list = [];
        foreach ($roomTagPullList['data'] as $key => $val) {
            $employees = $this->workEmployeeService->getWorkEmployeesById(explode(',', $val['employees']), ['name']);
            $rooms = json_decode($val['rooms'], true, 512, JSON_THROW_ON_ERROR);
            $num = 0;
            foreach (json_decode($val['wxTid'], true, 512, JSON_THROW_ON_ERROR) as $tid) {
                if ($tid['status'] === 0) {
                    ++$num;
                }
            }
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'employees' => array_column($employees, 'name'),
                'rooms' => array_column($rooms, 'name'),
                'invite_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdSendStatus($val['id'], [1]),
                'join_room_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdJoinRoom($val['id'], 1),
                'no_send_num' => $num,
                'no_invite_num' => $this->roomTagPullContactService->countRoomTagPullContactByRoomTagPullIdSendStatus($val['id'], [0]),
                'created_at' => $val['createdAt'],
            ];
        }
        $data['page']['total'] = $roomTagPullList['total'];
        $data['page']['totalPage'] = $roomTagPullList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
