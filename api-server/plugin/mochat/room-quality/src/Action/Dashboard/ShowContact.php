<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityRecordContract;
use Psr\Container\ContainerInterface;

/**
 * 群聊质检-详情-客户.
 *
 * Class Show.
 * @Controller
 */
class ShowContact
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

    /**
     * @Inject
     * @var RoomQualityRecordContract
     */
    protected $roomQualityRecordService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

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

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * Show constructor.
     */
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
     * @RequestMapping(path="/dashboard/roomQuality/showContact", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 验证参数
        $params = $this->request->all();
        $this->validated($params);
        $roomQuality = $this->roomQualityService->getRoomQualityById((int) $params['id'], ['rooms']);
        $rooms = json_decode($roomQuality['rooms'], true, 512, JSON_THROW_ON_ERROR);
        ## 总触发规则次数
        $data['quality_total_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id']);
        ## 今日总触发规则次数
        $data['quality_today_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id'], 0, date('Y-m-d'));
        ## 本周总触发规则次数
        $data['quality_week_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id'], 0, date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
        ## 本月总触发规则次数
        $data['quality_month_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id'], 0, date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00')));
        $list = [];
        foreach ($rooms as $k => $v) {
            ## 群信息
            $list[$k]['room_id'] = $v['id'];
            $list[$k]['room_name'] = $v['name'];
            $room = $this->workRoomService->getWorkRoomById((int) $v['id'], ['owner_id', 'status']);
            $employee = $this->workEmployeeService->getWorkEmployeeById($room['ownerId'], ['name']);
            $list[$k]['owner_name'] = $employee['name'];
            $list[$k]['status'] = $room['status'];
            $list[$k]['room_total_num'] = $this->workContactRoomService->countWorkContactRoomByRoomId((int) $v['id']);
            $list[$k]['room_employee_num'] = $this->workContactRoomService->countWorkContactRoomByRoomId((int) $v['id'], 1);
            ## 统计记录数据
            $list[$k]['quality_total_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id'], (int) $v['id']);
            $list[$k]['quality_today_num'] = $this->roomQualityRecordService->countRoomQualityRecordByQualityId((int) $params['id'], (int) $v['id'], date('Y-m-d'));
            $last_quality = $this->roomQualityRecordService->getRoomQualityRecordLastByQualityId((int) $v['id'], ['created_at']);
            $list[$k]['last_time'] = empty($last_quality) ? '-' : $last_quality['createdAt'];
        }
        return ['data' => $data, 'list' => $list];
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
            'id.required' => '质检id 必填',
            'id.integer' => '质检id 必须为整型',
        ];
    }
}
