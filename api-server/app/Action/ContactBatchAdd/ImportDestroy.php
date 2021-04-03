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

use App\Contract\ContactBatchAddImportRecordServiceInterface;
use App\Contract\ContactBatchAddImportServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-删除记录.
 *
 * Class Index.
 * @Controller
 */
class ImportDestroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddImportServiceInterface
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var ContactBatchAddImportRecordServiceInterface
     */
    protected $contactBatchAddImportRecordService;

    /**
     * @api(
     *      #apiRoute /contactBatchAdd/importDestroy
     *      #apiTitle 删除客户导入
     *      #apiMethod DELETE
     *      #apiName ContactBatchAddImportDestroy
     *      #apiDescription
     *      #apiGroup 批量添加客户
     *      #apiParam {Number} ID 导入客户批次ID
     *      #apiSuccess {Number} delRecordNum 删除成功批次数量
     *      #apiSuccess {Number} delContactNum 删除成功客户数量
     *      #apiSuccessExample {json} Success-Response:
     *      {
     *          "code": 200,
     *          "msg": "",
     *          "data": {
     *              "delRecordNum": 1,
     *              "delContactNum": 2
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
     * @RequestMapping(path="/contactBatchAdd/importDestroy", methods="delete")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id');

        $contactIds    = $this->getContactBatchAddImportIdsByRecordId($params['id']);
        $delRecordNum  = $this->contactBatchAddImportRecordService->deleteContactBatchAddImportRecord((int) $params['id']);
        $delContactNum = $this->contactBatchAddImportService->deleteContactBatchAddImports($contactIds);

        return [
            'delRecordNum'  => $delRecordNum,
            'delContactNum' => $delContactNum,
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

    protected function getContactBatchAddImportIdsByRecordId($recordId): array
    {
        $contact    = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhere([['record_id', '=', $recordId]], [], ['id']);
        $co         = collect($contact);
        $contactIds = $co->pluck('id');
        return $contactIds->toArray();
    }
}
