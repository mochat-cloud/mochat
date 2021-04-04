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
     *      #apiSuccess {Number} id 员工ID
     *      #apiSuccess {String} name 员工名
     *      #apiSuccess {Number} allotNum 分配客户数
     *      #apiSuccess {Number} toAddNum 待添加客户数
     *      #apiSuccess {Number} pendingNum 待通过客户数
     *      #apiSuccess {Number} passedNum 已通过客户数
     *      #apiSuccess {Number} recycleNum 回收客户数（按次）
     *      #apiSuccess {Number} completion 添加完成率数
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "current_page": 1,
     *              "data": [
     *                  {
     *                      "id": 1,
     *                      "name": "员工一",
     *                      "allotNum": 9,
     *                      "toAddNum": 9,
     *                      "pendingNum": 0,
     *                      "passedNum": 0,
     *                      "recycleNum": 4,
     *                      "completion": 23.34
     *                  }
     *              ],
     *              "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=1",
     *              "from": 1,
     *              "last_page": 2,
     *              "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
     *              "next_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
     *              "path": "http://127.0.0.1:9510/contactBatchAdd/dashboard",
     *              "per_page": 15,
     *              "prev_page_url": null,
     *              "to": 15,
     *              "total": 20
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
