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
use MoChat\Plugin\ContactTransfer\Logic\LogLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Log extends AbstractAction
{
    /**
     * @var LogLogic
     */
    protected $logLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(LogLogic $logLogic, RequestInterface $request)
    {
        $this->logLogic = $logLogic;
        $this->request = $request;
    }

    /**
     * 分配离职客服群接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactTransfer/log", methods="GET")
     */
    public function handle(): array
    {
        $params['mode'] = $this->request->input('mode');                        //模式 1离职已分配客户 2离职已分配群聊 3在职已分配客户
        $params['name'] = $this->request->input('name');                        //客户/群聊名称
        $params['employeeId'] = $this->request->input('employeeId');            //接替的员工的WxId
        $params['createTimeStart'] = $this->request->input('createTimeStart');  //分配时间开始
        $params['createTimeEnd'] = $this->request->input('createTimeEnd');      //分配时间结束

        if (! $params['name']) {
            $params['name'] = '';
        }
        if (! $params['employeeId']) {
            $params['employeeId'] = '';
        }
        if (! $params['createTimeStart']) {
            $params['createTimeStart'] = '';
        }
        if (! $params['createTimeEnd']) {
            $params['createTimeEnd'] = '';
        }

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        return $this->logLogic->getModeOneList($params);
    }
}
