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
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;

/**
 * 门店活码-数据分析-客户数据.
 * @Controller
 */
class ShowContact extends AbstractAction
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
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

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
     * @RequestMapping(path="/dashboard/shopCode/showContact", methods="get")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $params = $this->request->all();
        $this->validated($params);
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 接收参数
        $params = [
            'type' => $this->request->input('type'),
            'contactName' => $this->request->input('contactName'),
            'employeeId' => $this->request->input('employeeId'),
            'start_time' => $this->request->input('start_time'),
            'end_time' => $this->request->input('end_time'),
            'shopName' => $this->request->input('shopName'),
            'status' => $this->request->input('status'),
            'province' => $this->request->input('province'),
            'city' => $this->request->input('city'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        ## 店主
        if ((int) $params['type'] === 1) {
            return $this->shop($user, $params);
        }
        ## 门店群-待开发
        if ((int) $params['type'] === 2) {
            return $this->shopRoom($user, $params);
        }
        ## 城市群-带开发
        if ((int) $params['type'] === 3) {
            return $this->cityRoom($user, $params);
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
            'type' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => '类型 必填',
            'type.integer' => '类型 必须为整型',
        ];
    }

    /**
     * 店主.
     * @throws \JsonException
     */
    private function shop(array $user, array $params): array
    {
        $shopList = $this->shopCodeService->getShopCodeByCorpIdType($user['corpIds'][0], (int) $params['type'], '', ['id']);
        $shop = [];
        foreach ($shopList as $k => $v) {
            $shop[] = 'shopCode-' . $v['id'];
        }
        $contactList = $this->workContactService->getWorkContactsByStateSearch($user['corpIds'][0], $shop, $params);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data : $this->handleData($contactList);
    }

    /**
     * 门店群.
     * @throws \JsonException
     */
    private function shopRoom(array $user, array $params): array
    {
        $shopList = $this->shopCodeService->getShopCodeByCorpIdType($user['corpIds'][0], (int) $params['type'], '', ['id', 'qw_code']);
        $shop = [];
        foreach ($shopList as $k => $v) {
            $qwCode = json_decode($v['qwCode'], true, 512, JSON_THROW_ON_ERROR);
            $workRoomAutoPullId = $qwCode['workRoomAutoPullId'];
            $shop[] = 'workRoomAutoPullId-' . $workRoomAutoPullId;
        }
        $contactList = $this->workContactService->getWorkContactsByStateSearch($user['corpIds'][0], $shop, $params);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data : $this->handleData($contactList);
    }

    /**
     * 城市群.
     * @throws \JsonException
     */
    private function cityRoom(array $user, array $params): array
    {
        $shopList = $this->shopCodeService->getShopCodeByCorpIdType($user['corpIds'][0], (int) $params['type'], '', ['id', 'qw_code']);
        $shop = [];
        foreach ($shopList as $k => $v) {
            $qwCode = json_decode($v['qwCode'], true, 512, JSON_THROW_ON_ERROR);
            $workRoomAutoPullId = $qwCode['workRoomAutoPullId'];
            $shop[] = 'workRoomAutoPullId-' . $workRoomAutoPullId;
        }
        $contactList = $this->workContactService->getWorkContactsByStateSearch($user['corpIds'][0], $shop, $params);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data : $this->handleData($contactList);
    }

    /**
     * 数据处理.
     * @param array $contactList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $contactList): array
    {
        $list = [];
        foreach ($contactList['data'] as $key => $val) {
            $tag = $this->workContactTagPivotService->getWorkContactTagPivotsNameByContactId($val['contactId']);
            $list[$key] = [
                'contactId' => $val['contactId'],
                'contactName' => $val['contactName'],
                'avatar' => file_full_url($val['avatar']),
                'employee' => json_decode($val['employee'], true, 512, JSON_THROW_ON_ERROR),
                'createdAt' => $val['createdAt'],
                'shopName' => $val['shopName'],
                'tag' => $tag,
                'address' => $val['address'],
                'province' => $val['province'],
                'city' => $val['city'],
                'status' => $val['status'],
            ];
        }
        $data['page']['total'] = $contactList['total'];
        $data['page']['totalPage'] = $contactList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
