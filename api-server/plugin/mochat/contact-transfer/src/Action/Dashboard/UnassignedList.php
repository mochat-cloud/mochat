<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactTransfer\Logic\UnassignedListLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class UnassignedList extends AbstractAction
{
    /**
     * @var UnassignedListLogic
     */
    protected $unassignedListLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(UnassignedListLogic $unassignedListLogic, RequestInterface $request)
    {
        $this->unassignedListLogic = $unassignedListLogic;
        $this->request = $request;
    }

    /**
     * 离职待分配客户列表.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactTransfer/unassignedList", methods="GET")
     */
    public function handle(): array
    {
        $params['contactName'] = $this->request->input('contactName');      //客户昵称
        $params['employeeId'] = $this->request->input('employeeId');        //客服id
        $params['addTimeStart'] = $this->request->input('addTimeStart');    //添加起始时间
        $params['addTimeEnd'] = $this->request->input('addTimeEnd');        //添加结束时间

        $user = user();
        if (! $params['employeeId']) {
            $params['employeeId'] = '[]';
        }
        $params['employeeId'] = json_decode($params['employeeId'], true, 512, JSON_THROW_ON_ERROR);

        $params['corpId'] = $user['corpIds'][0];    //企业id
        $params['dataPermission'] = $user['dataPermission'];    //权限

        return $this->unassignedListLogic->getUnassignedList($params);
    }
}
