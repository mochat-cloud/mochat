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
use MoChat\Plugin\ShopCode\Contract\ShopCodeRecordContract;

/**
 * 门店活码-数据总览.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var ShopCodeRecordContract
     */
    protected $shopCodeRecordService;

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
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/shopCode/show", methods="get")
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
        ## 店主
        if ((int) $params['type'] === 1) {
            return $this->shop($user, $params);
        }
        ## 门店群-待开发
        if ((int) $params['type'] === 2) {
            return $this->shopRoom($user, $params);
        }
        ## 城市群-待开发
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
     */
    private function shop(array $user, array $params): array
    {
        $shopList = $this->shopCodeService->getShopCodeByCorpIdType($user['corpIds'][0], (int) $params['type'], '', ['id']);
        $shop = [];
        foreach ($shopList as $k => $v) {
            $shop[] = 'shopCode-' . $v['id'];
        }
        ## 今日数据
        $day = date('Y-m-d');
        $dataStatistics['today_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type'], $day);
        $dataStatistics['today_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1, $day);
        $dataStatistics['today_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2, $day);
        ## 总数据
        $dataStatistics['total_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type']);
        $dataStatistics['total_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1);
        $dataStatistics['total_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2);
        return [$dataStatistics];
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
        ## 今日数据
        $day = date('Y-m-d');
        $dataStatistics['today_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type'], $day);
        $dataStatistics['today_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1, $day);
        $dataStatistics['today_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2, $day);
        ## 总数据
        $dataStatistics['total_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type']);
        $dataStatistics['total_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1);
        $dataStatistics['total_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2);
        return [$dataStatistics];
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
        ## 今日数据
        $day = date('Y-m-d');
        $dataStatistics['today_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type'], $day);
        $dataStatistics['today_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1, $day);
        $dataStatistics['today_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2, $day);
        ## 总数据
        $dataStatistics['total_click_num'] = $this->shopCodeRecordService->countShopCodeRecordByCorpIdTypeCreatedAt($user['corpIds'][0], (int) $params['type']);
        $dataStatistics['total_add_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 1);
        $dataStatistics['total_loss_num'] = $this->workContactService->countWorkContactsByCorpIdStateStatus($user['corpIds'][0], $shop, 2);
        return [$dataStatistics];
    }
}
