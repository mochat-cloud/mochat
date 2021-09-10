<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Dashboard;

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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-列表
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $perPage;

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
     * @RequestMapping(path="/dashboard/roomFission/index", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 验证参数
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'name' => $this->request->input('name'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        $data = $this->handleParams($user, $params);

        return $this->getRoomFission($user, $data);
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
            $where[] = ['active_name', 'LIKE', '%' . $params['name'] . '%'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomFission(array $user, array $params): array
    {
        $columns = ['id', 'active_name', 'created_at', 'end_time'];
        $roomFissionList = $this->roomFissionService->getRoomFissionList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomFissionList['data']) ? $data : $this->handleData($user, $roomFissionList);
    }

    /**
     * 数据处理.
     * @param array $roomFissionList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $user, array $roomFissionList): array
    {
        $list = [];
        foreach ($roomFissionList['data'] as $key => $val) {
            $list[$key] = [
                'id' => $val['id'],
                'activeName' => $val['activeName'],
                'createdAt' => $val['createdAt'],
                'endTime' => $val['endTime'],
                'join_user_num' => 0,
                'finish_user_num' => 0,
                'status' => date('Y-m-d H:i:s') > $val['endTime'] ? 2 : 1,
            ];
        }
        $data['page']['total'] = $roomFissionList['total'];
        $data['page']['totalPage'] = $roomFissionList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
