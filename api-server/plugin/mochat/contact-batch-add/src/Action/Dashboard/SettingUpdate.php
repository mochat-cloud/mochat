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
use Hyperf\Validation\Rule;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddConfigContract;

/**
 * 导入客户-设置修改动作.
 *
 * Class SettingUpdate.
 * @Controller
 */
class SettingUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddConfigContract
     */
    protected $contactBatchAddConfigService;

    /**
     * @api(
     * @RequestMapping(path="/dashboard/contactBatchAdd/settingUpdate", methods="POST")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['pending_status'] = $this->request->input('pendingStatus');
        $params['pending_time_out'] = $this->request->input('pendingTimeOut');
        $params['pending_reminder_time'] = $this->request->input('pendingReminderTime');
        $params['pending_leader_id'] = $this->request->input('pendingLeaderId', 0);
        $params['undone_status'] = $this->request->input('undoneStatus');
        $params['undone_time_out'] = $this->request->input('undoneTimeOut');
        $params['undone_reminder_time'] = $this->request->input('undoneReminderTime');
        $params['recycle_status'] = $this->request->input('recycleStatus');
        $params['recycle_time_out'] = $this->request->input('recycleTimeOut');
        $this->validated($params);
        $bool = $this->contactBatchAddConfigService->updateContactBatchAddConfigByCorpId((int) user()['corpIds'][0], $params);
        return [
            'status' => (int) $bool,
        ];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'pending_status' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'pending_time_out' => 'required|numeric',
            'pending_reminder_time' => 'required|date_format:H:i:s',
            'pending_leader_id' => 'numeric',
            'undone_status' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'undone_time_out' => 'required|numeric',
            'undone_reminder_time' => 'required|date_format:H:i:s',
            'recycle_status' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'recycle_time_out' => 'required|numeric',
        ];
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
