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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactSop\Logic\LogStateLogic;

/**
 * @Controller
 */
class LogState extends AbstractAction
{
    /**
     * @var LogStateLogic
     */
    protected $logState;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(LogStateLogic $logState, RequestInterface $request)
    {
        $this->logState = $logState;
        $this->request = $request;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactSop/logState", methods="PUT")
     */
    public function handle(): array
    {
        $params['id'] = (int) $this->request->input('id');        //规则log id

        return $this->logState->handle($params);
    }
}
