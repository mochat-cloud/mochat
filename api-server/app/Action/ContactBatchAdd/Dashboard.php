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

use App\Logic\ContactBatchAdd\DashboardLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-统计数据.
 *
 * Class Index.
 * @Controller
 */
class Dashboard extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var DashboardLogic
     */
    protected $dashboardLogic;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/dashboard
     *      #apiTitle 数据统计
     *      #apiMethod GET
     *      #apiName ContactBatchAddDashboard
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiSuccess {Object} employees 员工列表
     *      #apiSuccess {Number} employees.id 员工ID
     *      #apiSuccess {String} employees.name 员工名
     *      #apiSuccess {Number} employees.allotNum 分配客户数
     *      #apiSuccess {Number} employees.toAddNum 待添加客户数
     *      #apiSuccess {Number} employees.pendingNum 待通过客户数
     *      #apiSuccess {Number} employees.passedNum 已通过客户数
     *      #apiSuccess {Number} employees.recycleNum 回收客户数（按次）
     *      #apiSuccess {Number} employees.completion 添加完成率数
     *      #apiSuccess {Object} dashboard 仪表盘统计数据
     *      #apiSuccess {Object} dashboard.contactNum 导入客户数量
     *      #apiSuccess {Object} dashboard.pendingNum 待分配客户数
     *      #apiSuccess {Object} dashboard.toAddNum 待添加客户数
     *      #apiSuccess {Object} dashboard.passedNum 已添加客户数
     *      #apiSuccess {Object} dashboard.completion 完成率
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "employees": {
     *                  "current_page": 1,
     *                  "data": [
     *                      {
     *                          "id": 11,
     *                          "name": "员工1",
     *                          "allotNum": 1,
     *                          "toAddNum": 2,
     *                          "pendingNum": 3,
     *                          "passedNum": 4,
     *                          "recycleNum": 5,
     *                          "completion": 23.42
     *                      }
     *                  ],
     *                  "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=1",
     *                  "from": 1,
     *                  "last_page": 2,
     *                  "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
     *                  "next_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
     *                  "path": "http://127.0.0.1:9510/contactBatchAdd/dashboard",
     *                  "per_page": 15,
     *                  "prev_page_url": null,
     *                  "to": 15,
     *                  "total": 20
     *              },
     *              "dashboard": {
     *                  "contactNum": 3,
     *                  "pendingNum": 1,
     *                  "toAddNum": 1,
     *                  "passedNum": 0,
     *                  "completion": 0
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
     * @RequestMapping(path="/contactBatchAdd/dashboard", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['corp_id'] = user()['corpIds'][0];
        return $this->dashboardLogic->handle($params);
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
