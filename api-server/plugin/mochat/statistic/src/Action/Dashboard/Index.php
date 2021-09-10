<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Statistic\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\Statistic\Logic\IndexLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(IndexLogic $indexLogic, RequestInterface $request)
    {
        $this->indexLogic = $indexLogic;
        $this->request = $request;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/statistic/index", methods="GET")
     */
    public function handle(): array
    {
        $params['startTime'] = $this->request->input('startTime');
        $params['endTime'] = $this->request->input('endTime');
        $params['employeeId'] = $this->request->input('employeeId');
        $params['mode'] = $this->request->input('mode');

        $todayData = $this->indexLogic->todayData();
        $timerDataTemp = $this->indexLogic->anyTimeOrEmployeesData($params);

        $timerData = [];

        foreach ($timerDataTemp as $date => $timerDatum) {
            $timerData[] = [
                'date' => $date,
                'total' => $timerDatum['total'],
                'add' => $timerDatum['add'],
                'loss' => $timerDatum['loss'],
                'net' => $timerDatum['net'],
            ];
        }

        $result = [];
        foreach ($timerData as $timerDatum) {
            if ($params['mode'] == 1) {
                $result[$timerDatum['date']] = $timerDatum['total'];
            }

            if ($params['mode'] == 2) {
                $result[$timerDatum['date']] = $timerDatum['add'];
            }

            if ($params['mode'] == 3) {
                $result[$timerDatum['date']] = $timerDatum['loss'];
            }

            if ($params['mode'] == 4) {
                $result[$timerDatum['date']] = $timerDatum['net'];
            }
        }

        return [
            'today' => $todayData,
            'table' => $result,
            'any' => $timerData,
        ];
    }
}
