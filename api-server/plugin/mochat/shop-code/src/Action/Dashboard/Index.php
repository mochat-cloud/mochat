<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Action\Dashboard;

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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-列表
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

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
     * @RequestMapping(path="/dashboard/shopCode/index", methods="get")
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
            'type' => $this->request->input('type'),
            'name' => $this->request->input('name'),
            'province' => $this->request->input('province'),
            'city' => $this->request->input('city'),
            'qw_code' => $this->request->input('qw_code'),
            'employee' => $this->request->input('employee'),
            'status' => $this->request->input('status'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        $data = $this->handleParams($user, $params);

        return $this->getRoomQuality($user, $data);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => 'type 必传',
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
        $where['type'] = (int) $params['type'];
        if (isset($params['name']) && ! empty($params['name'])) {
            $where[] = ['name', 'LIKE', '%' . $params['name'] . '%'];
        }
        if (isset($params['province']) && ! empty($params['province'])) {
            $where['province'] = $params['province'];
            $where['city'] = $params['city'];
        }
        if (isset($params['employee']) && ! empty($params['employee'])) {
            $where['employee->id'] = $params['employee'];
        }
        if (isset($params['qw_code']) && ! empty($params['qw_code'])) {
            $where[] = ['qw_code', 'LIKE', '%' . $params['qw_code'] . '%'];
        }
        if (isset($params['status']) && is_numeric($params['status'])) {
            $where['status'] = $params['status'];
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
    private function getRoomQuality(array $user, array $params): array
    {
        $columns = ['id', 'type', 'name', 'employee', 'qw_code', 'status', 'address', 'province', 'city'];
        $roomQualityList = $this->shopCodeService->getShopCodeList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roomQualityList['data']) ? $data : $this->handleData($user, $roomQualityList);
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
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'address' => $val['address'],
                'employee' => json_decode($val['employee'], true, 512, JSON_THROW_ON_ERROR),
                'qwCode' => ($val['type'] > 1) ? json_decode($val['qwCode'], true, 512, JSON_THROW_ON_ERROR) : $val['qwCode'],
                'province' => $val['province'],
                'city' => $val['city'],
                'status' => $val['status'],
            ];
        }
        $data['page']['total'] = $roomTagPullList['total'];
        $data['page']['totalPage'] = $roomTagPullList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
