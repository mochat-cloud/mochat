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
class ContactDetail
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
     * @RequestMapping(path="/dashboard/roomQuality/contactDetail", methods="get")
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
        $roomEmployee = $this->workContactRoomService->getWorkContactRoomsByRoomIdEmployee((int) $params['room_id'], ['employee_id']);
        foreach ($roomEmployee as $k => $v) {
            $employee = $this->workEmployeeService->getWorkEmployeeById($v['employeeId'], ['name', 'avatar', 'audit_status']);
            $roomEmployee[$k]['name'] = $employee['name'];
            $roomEmployee[$k]['avatar'] = file_full_url($employee['avatar']);
            $roomEmployee[$k]['audit_status'] = $employee['auditStatus'];
            $roomEmployee[$k]['corp_id'] = $user['corpIds'][0];
            $roomEmployee[$k]['employee_id'] = $v['employeeId'];
        }

        return ['room_employee' => $roomEmployee];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'room_id' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'room_id.required' => '群聊id 必填',
            'room_id.integer' => '群聊id 必须为整型',
        ];
    }
}
