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
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactTransfer\Logic\SaveUnassignedListLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class SaveUnassignedList extends AbstractAction
{
    /**
     * @var SaveUnassignedListLogic
     */
    protected $saveUnassignedListLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(SaveUnassignedListLogic $saveUnassignedListLogic, RequestInterface $request)
    {
        $this->saveUnassignedListLogic = $saveUnassignedListLogic;
        $this->request = $request;
    }

    /**
     * 同步离职待分配客户列表.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactTransfer/saveUnassignedList", methods="GET")
     */
    public function handle(): array
    {
        $user = user();
        $result = $this->saveUnassignedListLogic->SyncUnassignedList($user['corpIds'][0]);

        if ($result) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '同步失败');
        }
        return [];
    }
}
