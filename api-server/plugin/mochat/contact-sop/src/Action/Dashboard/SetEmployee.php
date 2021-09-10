<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Action\Dashboard;

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
use MoChat\Plugin\ContactSop\Logic\SetEmployeeLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class SetEmployee extends AbstractAction
{
    /**
     * @var SetEmployeeLogic
     */
    protected $setEmployeeLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(SetEmployeeLogic $setEmployeeLogic, RequestInterface $request)
    {
        $this->setEmployeeLogic = $setEmployeeLogic;
        $this->request = $request;
    }

    /**
     * 修改某个规则的使用成员.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/setEmployee", methods="PUT")
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id'); //规则id
        $params['employees'] = $this->request->input('employees'); //成员id json

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        $res = $this->setEmployeeLogic->handle($params);
        if ($res) {
            return [];
        }
        throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
    }
}
