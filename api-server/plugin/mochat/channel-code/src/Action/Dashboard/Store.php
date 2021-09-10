<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ChannelCode\Logic\StoreLogic;

/**
 * 新建渠道码.
 *
 * Class Store
 * @Controller
 */
class Store extends AbstractAction
{
    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/channelCode/store", methods="POST")
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function handle()
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //接收参数
        $params['baseInfo'] = $this->request->input('baseInfo');
        $params['drainageEmployee'] = $this->request->input('drainageEmployee');
        $params['welcomeMessage'] = $this->request->input('welcomeMessage');

        return $this->storeLogic->handle($params);
    }
}
