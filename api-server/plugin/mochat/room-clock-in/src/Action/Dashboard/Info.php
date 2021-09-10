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
 * 群打卡- 修改详情.
 *
 * Class Info.
 * @Controller
 */
class Info extends AbstractAction
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

    public function __construct(\Hyperf\HttpServer\Contract\RequestInterface $request, ClockInContract $clockInService, ClockInContactContract $clockInContactService, WorkEmployeeContract $workEmployeeService, ClockInContactRecordContract $clockInContactRecordService)
    {
        $this->request = $request;
        $this->clockInService = $clockInService;
        $this->clockInContactService = $clockInContactService;
        $this->workEmployeeService = $workEmployeeService;
        $this->clockInContactRecordService = $clockInContactRecordService;
    }

    /**
     * @RequestMapping(path="/dashboard/roomClockIn/info", methods="get")
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
        ## 数据详情
        $clockIn = $this->clockInService->getClockInById((int) $id, ['id', 'name', 'description', 'tasks', 'start_time', 'end_time', 'type', 'time_type', 'contact_clock_tags', 'employee_qrcode', 'corp_card_status', 'corp_card', 'point', 'create_user_id', 'created_at']);
        $clockIn['employeeQrcode'] = file_full_url($clockIn['employeeQrcode']);
        $corp = json_decode($clockIn['corpCard'], true);
        if (! empty($corp['logo'])) {
            $corp['logo'] = file_full_url($corp['logo']);
        }
        $clockIn['corpCard'] = $corp;
        $clockIn['tasks'] = json_decode($clockIn['tasks'], true, 512, JSON_THROW_ON_ERROR);
        $clockIn['contactClockTags'] = json_decode($clockIn['contactClockTags'], true, 512, JSON_THROW_ON_ERROR);
        return $clockIn;
    }
}
