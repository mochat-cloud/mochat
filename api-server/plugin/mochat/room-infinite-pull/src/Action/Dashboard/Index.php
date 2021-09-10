<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomInfinitePull\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomInfinitePull\Contract\RoomInfiniteContract;
use Psr\Container\ContainerInterface;

/**
 * 群聊质检-列表
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomInfiniteContract
     */
    protected $roomInfiniteService;

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
     * @Inject
     * @var UserContract
     */
    protected $userService;

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
     * @RequestMapping(path="/dashboard/roomInfinitePull/index", methods="get")
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

        return $this->getRoomQuality($data);
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
     * 获取群聊质检列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRoomQuality(array $params): array
    {
        $columns = ['id', 'name', 'avatar', 'qw_code', 'total_num', 'created_at'];
        $roomInfiniteList = $this->roomInfiniteService->getRoomInfiniteList($params['where'], $columns, $params['options']);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomInfiniteList['data']) ? $data : $this->handleData($roomInfiniteList);
    }

    /**
     * 数据处理.
     * @param array $roomInfiniteList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $roomInfiniteList): array
    {
        $list = [];
        foreach ($roomInfiniteList['data'] as $key => $val) {
            $qwCode = json_decode($val['qwCode'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($qwCode as $k => $v) {
                $qwCode[$k]['qrcode'] = file_full_url($v['qrcode']);
            }
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'avatar' => file_full_url($val['avatar']),
                'qwCode' => $qwCode,
                'link' => Url::getOperationBaseUrl() . '/roomInfinitePull?id=' . $val['id'],
                'total_num' => $val['totalNum'],
                'created_at' => $val['createdAt'],
            ];
        }
        $data['page']['total'] = $roomInfiniteList['total'];
        $data['page']['totalPage'] = $roomInfiniteList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
