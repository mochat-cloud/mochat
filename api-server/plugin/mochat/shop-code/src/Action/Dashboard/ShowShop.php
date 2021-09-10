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
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;

/**
 * 门店活码-数据分析-客户数据.
 * @Controller
 */
class ShowShop extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/shopCode/showShop", methods="get")
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 接收参数
        $params = [
            'type' => $this->request->input('type'),
            'employeeId' => $this->request->input('employeeId'),
            'shopName' => $this->request->input('shopName'),
            'status' => $this->request->input('status'),
            'province' => $this->request->input('province'),
            'city' => $this->request->input('city'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];
        $data = $this->handleParams($user, $params);
        ## 店主
        if ((int) $params['type'] === 1) {
            return $this->shop($user, $data, 1);
        }
        ## 门店群
        if ((int) $params['type'] === 2) {
            return $this->shop($user, $data, 2);
        }
        ## 城市群
        if ((int) $params['type'] === 3) {
            return $this->shop($user, $data, 3);
        }

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
            'id' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动id 必填',
            'id.integer' => '活动id 必须为整型',
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
        if (isset($params['employeeId']) && ! empty($params['employeeId'])) {
            $where['employee->id'] = $params['employeeId'];
        }
        if (isset($params['province']) && ! empty($params['province'])) {
            $where['province'] = $params['province'];
            $where['city'] = $params['city'];
        }
        if (isset($params['shopName']) && ! empty($params['shopName'])) {
            $where[] = ['name', 'LIKE', '%' . $params['shopName'] . '%'];
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
     * 店主.
     * @throws \JsonException
     */
    private function shop(array $user, array $params, int $type): array
    {
        $columns = ['id', 'name', 'type', 'employee', 'qw_code', 'address', 'province', 'city', 'status'];
        $shopList = $this->shopCodeService->getShopCodeList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($shopList['data']) ? $data : $this->handleData($user, $shopList, $type);
    }

    /**
     * 门店群.
     */
    private function shopRoom(array $user, array $params): array
    {
        return [];
    }

    /**
     * 城市群.
     */
    private function cityRoom(array $user, array $params): array
    {
        return [];
    }

    /**
     * 数据处理.
     * @param array $shopList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $user, array $shopList, int $type): array
    {
        $list = [];
        foreach ($shopList['data'] as $key => $val) {
            $shop = 'shopCode-' . $val['id'];
            if ($type > 1) {
                $qw_code = json_decode($val['qwCode'], true, 512, JSON_THROW_ON_ERROR);
                $workRoomAutoPullId = $qw_code['workRoomAutoPullId'];
                $shop = 'workRoomAutoPullId-' . $workRoomAutoPullId;
            }
            $list[$key] = [
                'name' => $val['name'],
                'city' => $val['city'],
                'address' => $val['address'],
                'employee' => json_decode($val['employee'], true, 512, JSON_THROW_ON_ERROR),
                'today_num' => $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], [$shop], 1, date('Y-m-d')),
                'total_num' => $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], [$shop], 1),
                'status' => $val['status'],
            ];
        }
        $data['page']['total'] = $shopList['total'];
        $data['page']['totalPage'] = $shopList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
