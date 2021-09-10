<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\User\Logic\Traits\UserTrait;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageIndexContract;
use MoChat\Framework\Action\AbstractAction;

/**
 * @Controller
 */
class FromUsers extends AbstractAction
{
    use UserTrait;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkMessageIndexContract
     */
    protected $workMsgIndexService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workMessage/fromUsers", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数.验证
        $corpId = $this->corpId();
        $name = $this->request->query('name', '');

        ## 已经存在的聊天员工
        $msgIndex = $this->workMsgIndexService->getWorkMessageIndicesUniqueColumns($corpId, ['from_id']);
        if (empty($msgIndex)) {
            return [];
        }

        ## 模糊搜索员工
        $data = $this->workEmployeeService->getWorkEmployeesByIdName(array_column($msgIndex, 'fromId'), $name, [
            'id', 'name', 'avatar',
        ]);

        return array_map(function ($item) {
            $item['avatar'] && $item['avatar'] = file_full_url($item['avatar']);
            return $item;
        }, $data);
    }
}
