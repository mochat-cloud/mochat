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
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-勾选客户提醒.
 *
 * Class Remind.
 * @Controller
 */
class Remind extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployee;

    /**
     * @RequestMapping(path="/dashboard/contactBatchAdd/remind", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params = $this->request->all();
        $corpId = user()['corpIds'][0];

        //校验参数
        $this->validated($params);
        $count = $this->contactBatchAddImportService->countContactBatchAddImportByRecordIdEmployee((int) $params['recordId'], (int) $params['employeeId']);
        $workAgentService = make(WorkAgentContract::class);
        $agent = $workAgentService->getWorkAgentRemindByCorpId((int) $corpId, ['id']);

        $employee = $this->workEmployee->getWorkEmployeeById((int) $params['employeeId']);
        $url = Url::getSidebarBaseUrl() . '/contactBatchAdd?agentId=' . $agent['id'] . '&batchId=' . $params['recordId'];
        $text = "【管理员提醒】您有客户未添加哦！\n" .
            "提醒事项：添加客户\n" .
            "客户数量：{$count}名\n" .
            "记得及时添加哦\n" .
            "<a href='{$url}'>点击查看详情</a>";
        $messageRemind = make(MessageRemind::class);
        $messageRemind->sendToEmployee(
            (int) $employee['corpId'],
            $employee['wxUserId'],
            'text',
            $text
        );
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
            'employeeId' => 'required',
            'recordId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'employeeId.required' => '客户id 必传',
            'recordId.required' => '批次号 必传',
        ];
    }
}
