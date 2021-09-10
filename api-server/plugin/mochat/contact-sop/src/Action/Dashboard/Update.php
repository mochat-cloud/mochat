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
use MoChat\Plugin\ContactSop\Logic\UpdateLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class Update extends AbstractAction
{
    /**
     * @var UpdateLogic
     */
    protected $updateLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(UpdateLogic $updateLogic, RequestInterface $request)
    {
        $this->updateLogic = $updateLogic;
        $this->request = $request;
    }

    /**
     * 编辑规则接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/update", methods="PUT")
     */
    public function handle(): array
    {
        $params['id'] = $this->request->input('id'); //规则id
        $params['name'] = $this->request->input('name'); //规则名称
        $params['employees'] = $this->request->input('employees'); //成员json
        $params['setting'] = $this->request->input('setting'); //设置json

        $user = user();

        $params['workEmployeeId'] = $user['workEmployeeId'];
        $params['corpId'] = $user['corpIds'][0];

        $res = $this->updateLogic->handle($params);
        if ($res) {
            return [];
        }
        throw new CommonException(ErrorCode::SERVER_ERROR, '编辑失败');
    }
}
