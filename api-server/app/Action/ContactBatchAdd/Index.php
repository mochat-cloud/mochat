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

use App\Logic\ContactBatchAdd\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Validation\Rule;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/index
     *      #apiTitle 客户列表
     *      #apiMethod GET
     *      #apiName ContactBatchAddIndex
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiParam {Number} [status] 状态0未分配1待添加2待通过3已添加
     *      #apiParam {String} [searchKey] 搜索关键字（客户手机号[四位数字及以上有效]/客户备注/员工名模糊搜索）.
     *      #apiParam {Number} [recordId] 指定导入批次ID（适用于导入批次查看详情）
     *      #apiSuccess {Number} id 导入客户ID
     *      #apiSuccess {Number} recordId 导入批次ID
     *      #apiSuccess {String} phone 导入客户手机号
     *      #apiSuccess {Datetime} uploadAt 导入时间
     *      #apiSuccess {Number} status 状态0未分配1待添加2待通过3已添加
     *      #apiSuccess {Datetime} addAt 添加客户时间
     *      #apiSuccess {Number} employeeId 员工ID
     *      #apiSuccess {Number} allotNum 分配次数
     *      #apiSuccess {String} remark 客户备注
     *      #apiSuccess {Array} tags 标签数组
     *      #apiSuccess {Number} tags.id 标签ID
     *      #apiSuccess {String} tags.name 标签名
     *      #apiSuccess {Array} allotEmployee 员工资料数组
     *      #apiSuccess {Number} allotEmployee.id 员工ID
     *      #apiSuccess {String} allotEmployee.name 员工名
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "current_page": 1,
     *              "data": [
     *                  {
     *                      "id": 45,
     *                      "recordId": 26,
     *                      "phone": "13900139000",
     *                      "uploadAt": "2021-03-31 17:58:22",
     *                      "status": 1,
     *                      "addAt": "2021-03-31 17:58:22",
     *                      "employeeId": 3,
     *                      "allotNum": 1,
     *                      "remark": "王武",
     *                      "tags": [
     *                          {
     *                              "id": 1,
     *                              "name": "标签名"
     *                          }
     *                      ],
     *                      "allotEmployee": {
     *                          "id": 3,
     *                          "name": "张员工"
     *                      }
     *                  }
     *              ],
     *              "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/index?page=1",
     *              "from": 1,
     *              "last_page": 1,
     *              "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/index?page=1",
     *              "next_page_url": null,
     *              "path": "http://127.0.0.1:9510/contactBatchAdd/index",
     *              "per_page": 15,
     *              "prev_page_url": null,
     *              "to": 1,
     *              "total": 1
     *          }
     *      }
     *      #apiErrorExample {json} Error-Response:
     *      {
     *        "code": "100014",
     *        "msg": "服务异常",
     *        "data": []
     *      }
     * )
     * @RequestMapping(path="/contactBatchAdd/index", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['status']    = $this->request->input('status', null);
        $params['searchKey'] = $this->request->input('searchKey', null);
        $params['recordId']  = $this->request->input('recordId', 0);
        $this->validated($params);
        $params['corpId'] = user()['corpIds'][0];

        return $this->indexLogic->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'status' => [
                Rule::in([null, '0', '1', '2', '3']),
            ],
            'searchKey' => 'max:255',
            'recordId'  => 'numeric',
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
