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
use MoChat\Plugin\ContactBatchAdd\Logic\DataStatisticLogic;

/**
 * 导入客户-统计数据.
 *
 * Class Index.
 * @Controller
 */
class DataStatistic extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var DataStatisticLogic
     */
    protected $dataStatisticLogic;

    /**
     * @RequestMapping(path="/dashboard/contactBatchAdd/dataStatistic", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['corpId'] = user()['corpIds'][0];
        $params['employeeId'] = $this->request->input('employeeId', []);
        $params['startTime'] = $this->request->input('startTime', '');
        $params['endTime'] = $this->request->input('endTime', '');
        return $this->dataStatisticLogic->handle($params);
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
