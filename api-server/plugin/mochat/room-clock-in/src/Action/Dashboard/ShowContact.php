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
use Laminas\Stdlib\RequestInterface;
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
 * Class ShowContact.
 * @Controller
 */
class ShowContact extends AbstractAction
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
     * @var int
     */
    protected $perPage;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(\Hyperf\HttpServer\Contract\RequestInterface $request, ClockInContract $clockInService, ClockInContactContract $clockInContactService, WorkEmployeeContract $workEmployeeService, ClockInContactRecordContract $clockInContactRecordService)
    {
        $this->request = $request;
        $this->clockInService = $clockInService;
        $this->clockInContactService = $clockInContactService;
        $this->workEmployeeService = $workEmployeeService;
        $this->clockInContactRecordService = $clockInContactRecordService;
    }

    /**
     * @RequestMapping(path="/dashboard/roomClockIn/showContact", methods="get")
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
        $params = $this->request->all();
        ## 处理参数
        $params = $this->handleParams($params);
        ## 查询数据
        return $this->getClockInContactList($params);
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
            'status' => 'required',
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
            'status.required' => '状态 必填',
        ];
    }

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $where['clock_in_id'] = $params['id'];
        empty($params['nickname']) || $where[] = ['nickname', 'LIKE', '%' . $params['nickname'] . '%'];
        if (! empty($params['total_day']) && $params['total_day'] > 0) {
            $where['total_day'] = $params['total_day'];
        }
        empty($params['city']) || $where[] = ['city', 'LIKE', '%' . $params['city'] . '%'];
        if ($params['status'] == 1) {
            $where['total_day'] = 0;
        }
        if ($params['status'] == 2) {
            $where[] = ['total_day', '>', 0];
            $where['status'] = 0;
        }
        if ($params['status'] == 3) {
            $where['status'] = 2;
        }

        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取群打卡客户列表.
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getClockInContactList(array $params): array
    {
        $columns = ['id', 'contact_id', 'nickname', 'avatar', 'updated_at', 'city', 'employee_ids', 'contact_clock_tags', 'total_day', 'series_day', 'status', 'clock_in_id'];
        $contactList = $this->clockInContactService->getClockInContactList($params['where'], $columns, $params['options']);
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
     * @param $contactList
     */
    private function handleData($contactList): array
    {
        $list = [];
        foreach ($contactList['data'] as $key => $val) {
            $list[$key] = [
                'id' => $val['id'],
                'nickname' => $val['nickname'],
                'avatar' => file_full_url($val['avatar']),
                'contact_type' => $val['contactId'] > 0 ? '企业客户' : '非企业客户',
                'clock_time' => $this->clockTime((int) $val['clockInId'], (int) $val['id']),
                'city' => $val['city'],
                'employees' => $this->employee($val['employeeIds']),
                'contact_clock_tags' => empty($val['contactClockTags']) ? '' : array_column(json_decode($val['contactClockTags'], true), 'tagname'),
                'total_day' => $val['totalDay'],
                'series_day' => $val['seriesDay'],
                'user_action' => $this->userAction((int) $val['totalDay'], (int) $val['status']),
            ];
        }
        $data['page']['total'] = $contactList['total'];
        $data['page']['totalPage'] = $contactList['last_page'];
        $data['list'] = $list;

        return $data;
    }

    /**
     * 最近打卡时间.
     */
    private function clockTime(int $clockInId, int $contactId): string
    {
        $record = $this->clockInContactRecordService->getClockInContactRecordLastByClockInIdContactId((int) $clockInId, (int) $contactId, ['day']);
        return empty($record) ? '-' : $record['day'];
    }

    /**
     * 企业员工.
     * @param $employee_ids
     */
    private function employee($employee_ids): array
    {
        $employee = $this->workEmployeeService->getWorkEmployeesById(explode(',', $employee_ids), ['name', 'avatar']);
        foreach ($employee as $key => $val) {
            $employee[$key]['avatar'] = file_full_url($val['avatar']);
        }
        return $employee;
    }

    /**
     * 用户行为.
     */
    private function userAction(int $total_day, int $status): string
    {
        if ($total_day == 0) {
            return '进入页面未参与';
        }
        if ($total_day > 0 && $status == 0) {
            return '参与打卡未完成';
        }
        if ($status == 1) {
            return '已完成';
        }
        return 'aa' . $status;
    }
}
