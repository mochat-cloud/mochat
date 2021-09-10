<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Action\Sidebar;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 企微提醒-客户详情.
 *
 * Class ContactBatchAddDetail
 * @Controller
 */
class Detail extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImport;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeContract;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/contactBatchAdd/detail", methods="get")
     */
    public function handle(): array
    {
        //接收参数
        $params['employeeId'] = (int) user()['id'];
        $params['batchId'] = (int) $this->request->input('batchId');
        $params['status'] = (int) $this->request->input('status', 4);
        //校验参数
        $this->validated($params);
        $employee = $this->workEmployeeContract->getWorkEmployeeById($params['employeeId']);
        $list = $this->contactBatchAddImport->getContactBatchAddImportByRecordIdEmployeeId($params['batchId'], $params['employeeId'], $params['status'], ['phone', 'status']);
        return [
            'employeeName' => $employee['name'],
            'count' => count($list),
            'list' => $list,
        ];
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'batchId' => 'required',
            'employeeId' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'batchId.required' => '批次号 必传',
            'employeeId.required' => '员工id 必填',
        ];
    }
}
