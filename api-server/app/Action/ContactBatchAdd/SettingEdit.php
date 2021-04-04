<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactBatchAdd;

use App\Contract\ContactBatchAddConfigServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-设置修改页面.
 *
 * Class Index.
 * @Controller
 */
class SettingEdit extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddConfigServiceInterface
     */
    protected $contactBatchAddConfigService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/settingEdit
     *      #apiTitle 获取设置
     *      #apiMethod GET
     *      #apiName ContactBatchAddSettingEdit
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiSuccess {Number} pendingStatus 待处理客户提醒开关0关1开
     *      #apiSuccess {Number} pendingTimeOut 待处理客户提醒超时天数
     *      #apiSuccess {Time} pendingReminderTime 待处理客户提醒时间 示例（13:01:01）
     *      #apiSuccess {Number} pendingLeaderId 待处理客户提醒-管理员ID
     *      #apiSuccess {Number} undoneStatus 成员未添加客户提醒开关0关1开
     *      #apiSuccess {Number} undoneTimeOut 成员未添加客户提醒超时天数
     *      #apiSuccess {Time} undoneReminderTime 成员未添加客户提醒时间 示例（13:01:01）
     *      #apiSuccess {Number} recycleStatus 回收客户开关0关1开
     *      #apiSuccess {Number} recycleTimeOut 客户超过天数回收
     *      #apiSuccess {Object} pendingLeader 员工信息
     *      #apiSuccess {Object} pendingLeader.id 员工ID
     *      #apiSuccess {Object} pendingLeader.name 员工名
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "pendingStatus": 1,
     *              "pendingTimeOut": 1,
     *              "pendingReminderTime": "13:00:01",
     *              "pendingLeaderId": 1,
     *              "undoneStatus": 1,
     *              "undoneTimeOut": 1,
     *              "undoneReminderTime": "13:00:02",
     *              "recycleStatus": 1,
     *              "recycleTimeOut": 1,
     *              "pendingLeader": {
     *                  "id": 1,
     *                  "name": "员工一"
     *              }
     *          }
     *      }
     *      #apiErrorExample {json} Error-Response:
     *      {
     *        "code": "100014",
     *        "msg": "服务异常",
     *        "data": []
     *      }
     * )
     *
     * @RequestMapping(path="/contactBatchAdd/settingEdit", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $corpId = user()['corpIds'][0];

        $result = $this->contactBatchAddConfigService->getContactBatchAddConfigByCorpId($corpId, [
            'pending_status', 'pending_time_out', 'pending_reminder_time', 'pending_leader_id', 'undone_status',
            'undone_time_out', 'undone_reminder_time', 'recycle_status', 'recycle_time_out',
        ]);
        $result = $result ?: [
            'pendingStatus'       => 0,
            'pendingTimeOut'      => 0,
            'pendingReminderTime' => '00:00:00',
            'pendingLeaderId'     => 0,
            'pendingLeader'       => [],
            'undoneStatus'        => 0,
            'undoneTimeOut'       => 0,
            'undoneReminderTime'  => '00:00:00',
            'recycleStatus'       => 0,
            'recycleTimeOut'      => 0,
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
