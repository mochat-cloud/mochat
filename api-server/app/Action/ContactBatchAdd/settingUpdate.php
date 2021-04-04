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
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Validation\Rule;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-设置修改动作.
 *
 * Class Index.
 * @Controller
 */
class SettingUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddConfigServiceInterface
     */
    protected $contactBatchAddConfigService;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/settingUpdate
     *      #apiTitle 修改设置
     *      #apiMethod POST
     *      #apiName ContactBatchAddSettingUpdate
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiParam {Number} pendingStatus 待处理客户提醒开关0关1开
     *      #apiParam {Number} pendingTimeOut 待处理客户提醒超时天数
     *      #apiParam {Time} pendingReminderTime 待处理客户提醒时间 示例（13:01:01）
     *      #apiParam {Number} [pendingLeaderId] 待处理客户提醒管理员ID
     *      #apiParam {Number} undoneStatus 成员未添加客户提醒开关0关1开
     *      #apiParam {Number} undoneTimeOut 成员未添加客户提醒超时天数
     *      #apiParam {Time} undoneReminderTime 成员未添加客户提醒时间 示例（13:01:01）
     *      #apiParam {Number} recycleStatus 回收客户开关0关1开
     *      #apiParam {Number} recycleTimeOut 客户超过天数回收
     *      #apiSuccess {Number} status 保存成功1失败0
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "status": 1
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
     * @RequestMapping(path="/contactBatchAdd/settingUpdate", methods="POST")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['pending_status']        = $this->request->input('pendingStatus');
        $params['pending_time_out']      = $this->request->input('pendingTimeOut');
        $params['pending_reminder_time'] = $this->request->input('pendingReminderTime');
        $params['pending_leader_id']     = $this->request->input('pendingLeaderId', 0);
        $params['undone_status']         = $this->request->input('undoneStatus');
        $params['undone_time_out']       = $this->request->input('undoneTimeOut');
        $params['undone_reminder_time']  = $this->request->input('undoneReminderTime');
        $params['recycle_status']        = $this->request->input('recycleStatus');
        $params['recycle_time_out']      = $this->request->input('recycleTimeOut');
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
            'pending_time_out'      => 'required|numeric',
            'pending_reminder_time' => 'required|date_format:H:i:s',
            'pending_leader_id'     => 'numeric',
            'undone_status'         => [
                'required',
                Rule::in(['0', '1']),
            ],
            'undone_time_out'      => 'required|numeric',
            'undone_reminder_time' => 'required|date_format:H:i:s',
            'recycle_status'       => [
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
