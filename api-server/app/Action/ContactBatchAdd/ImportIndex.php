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

use App\Logic\ContactBatchAdd\ImportIndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-导入记录.
 *
 * Class Index.
 * @Controller
 */
class ImportIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ImportIndexLogic
     */
    private $importIndexLogic;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/importIndex
     *      #apiTitle 导入记录
     *      #apiMethod GET
     *      #apiName ContactBatchAddImportIndex
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiSuccess {Number} id 导入ID
     *      #apiSuccess {Number} corpId 企业ID
     *      #apiSuccess {String} title 标题
     *      #apiSuccess {Datetime} uploadAt 上传时间
     *      #apiSuccess {Array} allotEmployee 分配员工数组
     *      #apiSuccess {Number} allotEmployee.id 员工ID
     *      #apiSuccess {String} allotEmployee.name 员工名
     *      #apiSuccess {Array} tags 标签数组
     *      #apiSuccess {Number} tags.id 标签ID
     *      #apiSuccess {String} tags.name 标签名
     *      #apiSuccess {Number} importNum 导入数量
     *      #apiSuccess {Number} addNum 成功添加数量
     *      #apiSuccess {String} fileName 导入文件名
     *      #apiSuccess {String} fileUrl 导入文件URL
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "current_page": 1,
     *              "data": [
     *                  {
     *                      "id": 30,
     *                      "corpId": 1,
     *                      "title": "任务标题",
     *                      "uploadAt": "2021-04-02 17:41:50",
     *                      "allotEmployee": [
     *                          {
     *                              "id": 11,
     *                              "name": "员工一"
     *                          },
     *                          {
     *                              "id": 2,
     *                              "name": "员工二"
     *                          }
     *                      ],
     *                      "tags": [
     *                          {
     *                              "id": 2,
     *                              "name": "标签名"
     *                          }
     *                      ],
     *                      "importNum": 3,
     *                      "addNum": 0,
     *                      "fileName": "10af4acb8ed2e9fd923570008b06b702.xlsx",
     *                      "fileUrl": "http://xxx.com/xx.xlsx"
     *                  }
     *              ],
     *              "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=1",
     *              "from": 1,
     *              "last_page": 2,
     *              "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=2",
     *              "next_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=2",
     *              "path": "http://127.0.0.1:9510/contactBatchAdd/importIndex",
     *              "per_page": 15,
     *              "prev_page_url": null,
     *              "to": 15,
     *              "total": 27
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
     * @RequestMapping(path="/contactBatchAdd/importIndex", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $corpIds = user()['corpIds'];
        return $this->importIndexLogic->handle($corpIds[0]);
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
