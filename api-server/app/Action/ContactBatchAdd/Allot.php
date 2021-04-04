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

use App\Logic\ContactBatchAdd\AllotLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-勾选客户分配员工.
 *
 * Class Index.
 * @Controller
 */
class Allot extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AllotLogic
     */
    protected $allotLogic;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/allot
     *      #apiTitle 分配客户
     *      #apiMethod POST
     *      #apiName ContactBatchAddAllot
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiParam {Number[]} id 修改导入客户ID数组
     *      #apiParam {Number[]} employeeId 分配员工ID
     *      #apiSuccess {Number} updateNum 更新成功数量
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "updateNum": 1
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
     * @RequestMapping(path="/contactBatchAdd/allot", methods="post")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user                 = user();
        $params['id']         = $this->request->input('id');
        $params['employeeId'] = $this->request->input('employeeId');
        $this->validated($params);

        return $this->allotLogic->handle($params, $user);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id'         => 'required|array',
            'employeeId' => 'required|numeric',
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
