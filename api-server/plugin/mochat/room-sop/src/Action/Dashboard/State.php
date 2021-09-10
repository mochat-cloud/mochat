<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomSop\Logic\StateLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class State extends AbstractAction
{
    /**
     * @var StateLogic
     */
    protected $stateLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(StateLogic $stateLogic, RequestInterface $request)
    {
        $this->stateLogic = $stateLogic;
        $this->request = $request;
    }

    /**
     * 修改某个规则的开关状态
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomSop/state", methods="PUT")
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id'); //规则id
        $params['state'] = $this->request->input('state'); //状态：0关、1开

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        $res = $this->stateLogic->handle($params);
        if ($res) {
            return [];
        }
        throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
    }
}
