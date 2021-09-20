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
class DayDetail extends AbstractAction
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
     * @RequestMapping(path="/dashboard/roomClockIn/dayDetail", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        ## 查询数据
        return $this->handleData($params);
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
            'contact_id' => 'required',
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
            'contact_id' => '客户ID 必填',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleData($params): array
    {
        ## 数据统计
        $clockIn = $this->clockInService->getClockInById((int) $params['id'], ['type', 'tasks']);
        $contactRecord = $this->clockInContactRecordService->getClockInContactRecordsByClockInIdContactId((int) $params['id'], (int) $params['contact_id'], ['day']);
        $contact = $this->clockInContactService->getClockInContactById((int) $params['contact_id'], ['total_day', 'series_day']);
        $task = [];
        foreach (json_decode($clockIn['tasks'], true, 512, JSON_THROW_ON_ERROR) as $k => $v) {
            ## 连续打卡
            if ((int) $clockIn['type'] === 1) {
                if ((int) $contact['seriesDay'] >= (int) $v['count']) {
                    $task[] = '连续打卡' . $v['count'] . '天任务完成';
                } else {
                    $num = (int) $v['count'] - (int) $contact['seriesDay'];
                    $task[] = '距离连续打卡' . $v['count'] . '天还需' . $num . '天';
                }
            }
            ## 累计打卡
            if ((int) $clockIn['type'] === 2) {
                if ((int) $contact['totalDay'] >= (int) $v['count']) {
                    $task[] = '累计打卡' . $v['count'] . '天任务完成';
                } else {
                    $num = (int) $v['count'] - (int) $contact['seriesDay'];
                    $task[] = '距离累计打卡' . $v['count'] . '天还需' . $num . '天';
                }
            }
        }
        ## 数据详情
        return [
            'total_day' => (int) $clockIn['type'] === 1 ? $contact['seriesDay'] : $contact['totalDay'],
            'start_time' => empty($contactRecord) ? '' : $contactRecord[0]['day'],
            'end_time' => empty($contactRecord) ? '' : end($contactRecord)['day'],
            'task' => $task,
            'list' => $this->handleRecord($contactRecord),
        ];
    }

    private function handleRecord(array $contactRecord): array
    {
        $data = ['01' => [], '02' => [], '03' => [], '04' => [], '05' => [], '06' => [], '07' => [], '08' => [], '09' => [], '10' => [], '11' => [], '12' => []];
        foreach ($contactRecord as $item) {
            $month = date('m', strtotime($item['day']));
            $data[$month][] = date('Y-m-d', strtotime($item['day']));
        }
        return array_values($data);
    }
}
