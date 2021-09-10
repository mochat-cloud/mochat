<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkEmployee\Action\Dashboard;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkEmployee\QueueService\EmployeeApply;
use MoChat\Framework\Action\AbstractAction;

/**
 * 同步企业成员 - 页面.
 * @Controller
 */
class SyncEmployee extends AbstractAction
{
    /**
     * @var EmployeeApply
     */
    protected $employeeApply;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workEmployee/synEmployee", methods="PUT")
     */
    public function handle()
    {
        $params['corpIds'] = user()['corpIds'];
        //同步成员信息
        make(EmployeeApply::class)->handle($params['corpIds']);
    }
}
