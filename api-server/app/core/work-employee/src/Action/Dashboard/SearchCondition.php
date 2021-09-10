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
use MoChat\App\Corp\Constants\WorkUpdateTime\Type;
use MoChat\App\Corp\Contract\WorkUpdateTimeContract;
use MoChat\App\WorkEmployee\Constants\ContactAuth;
use MoChat\App\WorkEmployee\Constants\Status;
use MoChat\Framework\Action\AbstractAction;

/**
 * 查询 - 成员列表搜索条件数据.
 * @Controller
 */
class SearchCondition extends AbstractAction
{
    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workEmployee/searchCondition", methods="GET")
     */
    public function handle(): array
    {
        // 响应参数处理
        $data['status'] = array_map(function ($item) {
            return [
                'id' => $item,
                'name' => Status::getMessage($item),
            ];
        }, Status::$optionData);
        $data['contactAuth'] = array_map(function ($item) {
            return [
                'id' => $item,
                'name' => ContactAuth::getMessage($item),
            ];
        }, ContactAuth::$optionData);
        // 获取成员最新同步时间
        $syncTimeData = $this->container->get(WorkUpdateTimeContract::class)
            ->getWorkUpdateTimeByCorpIdType(user()['corpIds'], Type::EMPLOYEE, ['last_update_time']);
        $data['syncTime'] = empty($syncTimeData) ? '' : end($syncTimeData)['lastUpdateTime'];
        unset($syncTimeData);
        return $data;
    }
}
