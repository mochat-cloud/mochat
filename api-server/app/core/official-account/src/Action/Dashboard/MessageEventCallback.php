<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\OfficialAccount\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\Framework\Action\AbstractAction;
use Psr\Container\ContainerInterface;

/**
 * 授权变更通知推送
 * @Controller
 */
class MessageEventCallback extends AbstractAction
{
    /**
     * @Inject
     * @var OfficialAccountContract
     */
    protected $officialAccountService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @RequestMapping(path="/dashboard/officialAccount/messageEventCallback", methods="get,post")
     */
    public function handle()
    {
        $params = $this->request->all();
    }
}
