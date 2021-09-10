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
use MoChat\Plugin\RoomSop\Logic\StoreLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Store extends AbstractAction
{
    /**
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(StoreLogic $storeLogic, RequestInterface $request)
    {
        $this->storeLogic = $storeLogic;
        $this->request = $request;
    }

    /**
     * 新增规则接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomSop/store", methods="POST")
     */
    public function handle(): array
    {
        $params['name'] = $this->request->input('name'); //规则名称
        $params['setting'] = $this->request->input('setting'); //设置json

        $user = user();

        $params['workEmployeeId'] = $user['workEmployeeId'];
        $params['corpId'] = $user['corpIds'][0];
        $params['userId'] = $user['id'];

        $res = $this->storeLogic->handle($params);
        if ($res) {
            return [];
        }
        throw new CommonException(ErrorCode::SERVER_ERROR, '添加失败');
    }
}
