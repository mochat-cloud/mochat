<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddConfigContract;
use MoChat\Plugin\ContactBatchAdd\Logic\RecycleContactLogic;
use MoChat\Plugin\ContactBatchAdd\Model\ContactBatchAddConfig;

/**
 * 导入客户-设置修改页面.
 *
 * Class SettingEdit.
 * @Controller
 */
class SettingEdit extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddConfigContract
     */
    protected $contactBatchAddConfigService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @RequestMapping(path="/dashboard/contactBatchAdd/settingEdit", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
//        return (new RecycleContactLogic())->handle(ContactBatchAddConfig::find(2)->toArray());
        $corpId = user()['corpIds'][0];

        $result = $this->contactBatchAddConfigService->getContactBatchAddConfigByCorpId($corpId, [
            'pending_status', 'pending_time_out', 'pending_reminder_time', 'pending_leader_id', 'undone_status',
            'undone_time_out', 'undone_reminder_time', 'recycle_status', 'recycle_time_out',
        ]);
        $result = $result ?: [
            'pendingStatus' => 0,
            'pendingTimeOut' => 0,
            'pendingReminderTime' => '00:00:00',
            'pendingLeaderId' => 0,
            'pendingLeader' => [],
            'undoneStatus' => 0,
            'undoneTimeOut' => 0,
            'undoneReminderTime' => '00:00:00',
            'recycleStatus' => 0,
            'recycleTimeOut' => 0,
        ];
        $result['pendingLeader'] = $result['pendingLeaderId'] ? $this->workEmployeeService->getWorkEmployeeById($result['pendingLeaderId'], ['id', 'name']) : [];
        return $result;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
