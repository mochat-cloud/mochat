<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Dashboard;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkContact\Constants\AddWay;

/**
 * 客户来源.
 *
 * Class Source
 * @Controller
 */
class Source
{
    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workContact/source", methods="get")
     */
    public function handle()
    {
        $res = AddWay::$Enum;

        $data = [];
        foreach ($res as $key => $val) {
            $data[] = [
                'addWay' => $key,
                'addWayText' => $val,
            ];
        }

        return $data;
    }
}
