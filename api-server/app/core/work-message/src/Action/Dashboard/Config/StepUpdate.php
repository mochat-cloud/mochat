<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Action\Dashboard\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkMessage\Action\Dashboard\Config\Traits\RequestTrait;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 修改 - 页面.
 * @Controller
 */
class StepUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workMessageConfig/stepUpdate", methods="PUT")
     */
    public function handle(): array
    {
        $params['chatApplyStatus'] = (int) $this->request->post('chatApplyStatus', 0);
        ++$params['chatApplyStatus'];
        ## 第二步骤
        if ($params['chatApplyStatus'] === 3) {
            $this->validated($params, 'stepSecond');
        } elseif ($params['chatApplyStatus'] === 4) {
            ## 第三步骤
            $params = $this->request->inputs(['chatWhitelistIp', 'chatRsaKey', 'chatSecret', 'chatStatus', 'chatApplyStatus']);
            $this->validated($params, 'stepThird');
            ++$params['chatApplyStatus'];
            ## 数据格式处理
            $params['chatWhitelistIp'] = json_encode($params['chatWhitelistIp'], JSON_UNESCAPED_UNICODE);
            $params['chatRsaKey'] = json_encode($params['chatRsaKey'], JSON_UNESCAPED_UNICODE);
        } else {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '申请进度错误');
        }

        ## 修改动作
        $messageConfigService = $this->container->get(WorkMessageConfigContract::class);
        $id = $messageConfigService->getWorkMessageConfigByCorpId(user('corpIds')[0], ['id'])['id'];
        $messageConfigService->updateWorkMessageConfigById($id, $params);

        return [];
    }
}
