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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportRecordContract;

/**
 * 导入客户-删除记录.
 *
 * Class ImportDestroy.
 * @Controller
 */
class ImportDestroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var ContactBatchAddImportRecordContract
     */
    protected $contactBatchAddImportRecordService;

    /**
     * @RequestMapping(path="/dashboard/contactBatchAdd/importDestroy", methods="delete")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id');

        $contactIds = $this->getContactBatchAddImportIdsByRecordId($params['id']);
        $delRecordNum = $this->contactBatchAddImportRecordService->deleteContactBatchAddImportRecord((int) $params['id']);
        $delContactNum = $this->contactBatchAddImportService->deleteContactBatchAddImports($contactIds);

        return [
            'delRecordNum' => $delRecordNum,
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
        $contact = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhere([['record_id', '=', $recordId]], [], ['id']);
        $co = collect($contact);
        $contactIds = $co->pluck('id');
        return $contactIds->toArray();
    }
}
