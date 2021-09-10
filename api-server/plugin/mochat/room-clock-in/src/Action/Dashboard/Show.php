<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Action\Dashboard;

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
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactRecordContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;

/**
 * 群打卡- 详情.
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
     * @var ClockInContract
     */
    protected $clockInService;

    /**
     * @Inject
     * @var ClockInContactContract
     */
    protected $clockInContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ClockInContactRecordContract
     */
    protected $clockInContactRecordService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(RequestInterface $request, ClockInContract $clockInService, ClockInContactContract $clockInContactService, WorkEmployeeContract $workEmployeeService, ClockInContactRecordContract $clockInContactRecordService)
    {
        $this->request = $request;
        $this->clockInService = $clockInService;
        $this->clockInContactService = $clockInContactService;
        $this->workEmployeeService = $workEmployeeService;
        $this->clockInContactRecordService = $clockInContactRecordService;
    }

    /**
     * @RequestMapping(path="/dashboard/roomClockIn/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData($id);
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

    private function handleData($id): array
    {
        ## 数据统计
        $dataStatistics['today_user'] = $this->clockInContactRecordService->countInContactRecordTodayByClockInId((int) $id);
        $totalDay = $this->clockInContactService->sumClockInContactTotalDayByClockInId((int) $id);
        $dataStatistics['total_user'] = $this->clockInContactService->countClockInContactByClockInId((int) $id);
        $dataStatistics['average_day'] = $totalDay > 0 ? ceil($totalDay / $dataStatistics['total_user']) : 0;

        ## 数据详情
        return [
            'city' => $this->clockInContactService->getClockInContactCityById((int) $id, ['city']),
            'clockIn' => $this->clockIn($id),
            'data_statistics' => $dataStatistics,
        ];
    }

    private function clockIn($id): array
    {
        ## 群打卡信息
        $clockIn = $this->clockInService->getClockInById((int) $id, ['id', 'name', 'description', 'tasks', 'start_time', 'end_time', 'type', 'time_type', 'contact_clock_tags', 'create_user_id', 'created_at']);
        $clockIn['time'] = '永久有效';
        if ($clockIn['timeType'] == 2) {
            $clockIn['time'] = $clockIn['startTime'] . '-' . $clockIn['endTime'];
        }
        $clockIn['status'] = '进行中';
        $date = date('Y-m-d H:i:s');
        if ($clockIn['timeType'] == 2) {
            if ($clockIn['startTime'] >= $date) {
                $clockIn['status'] = '未开始';
            }
            if ($clockIn['startTime'] < $date && $clockIn['endTime'] > $date) {
                $clockIn['status'] = '进行中';
            }
            if ($clockIn['endTime'] <= $date) {
                $clockIn['status'] = '已结束';
            }
        }
        $clockIn['type'] = $clockIn['type'] == 1 ? '连续打卡' : '累计打卡';
        $clockIn['contactClockTags'] = empty($clockIn['contactClockTags']) ? '' : array_column(json_decode($clockIn['contactClockTags']), 'tags');
        //处理创建者信息
        $username = $this->userService->getUserById($clockIn['createUserId']);
        $clockIn['nickname'] = isset($username['name']) ? $username['name'] : '';
        $clockIn['task_count'] = count(json_decode($clockIn['tasks'], true));
        unset($clockIn['startTime'], $clockIn['endTime'], $clockIn['timeType'], $clockIn['tasks'], $clockIn['createUserId']);
        return $clockIn;
    }
}
