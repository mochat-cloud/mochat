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

use App\Contract\ContactBatchAddImportServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-删除客户.
 *
 * Class Index.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddImportServiceInterface
     */
    protected $contactBatchAddImportService;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/destroy
     *      #apiTitle 删除客户
     *      #apiMethod DELETE
     *      #apiName ContactBatchAddDestroy
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiParam {Number} id 导入客户ID
     *      #apiSuccess {Number} delNum 删除成功数量
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "delNum": 1
     *           }
     *      }
     *      #apiErrorExample {json} Error-Response:
     *      {
     *        "code": "100014",
     *        "msg": "服务异常",
     *        "data": []
     *      }
     * )
     *
     * @RequestMapping(path="/contactBatchAdd/destroy", methods="DELETE")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id');
        $delNum       = $this->contactBatchAddImportService->deleteContactBatchAddImport((int) $params['id']);
        return [
            'delNum' => $delNum,
        ];
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
