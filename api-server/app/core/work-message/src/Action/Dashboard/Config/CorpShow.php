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

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 查询 - 企业信息.
 * @Controller
 */
class CorpShow extends AbstractAction
{
    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workMessageConfig/corpShow", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数
        $corpId = (int) $this->request->input('corpId', 0);
        if (! in_array($corpId, user('corpIds'), true)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该用户无此企业权限');
        }

        ## 企业基本信息
        $corpService = $this->container->get(CorpContract::class);
        $corpData = $corpService->getCorpById($corpId, ['id', 'name', 'wx_corpid', 'social_code']);
        if (empty($corpData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该企业不存在');
        }

        ## 企业配置信息
        $messageConfigService = $this->container->get(WorkMessageConfigContract::class);
        $configData = $messageConfigService->getWorkMessageConfigByCorpId($corpId, [
            'id', 'corp_id', 'chat_admin', 'chat_admin_phone', 'chat_admin_idcard', 'chat_apply_status',
        ]);
        if (empty($configData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该企业未配置，请先配置');
        }

        return array_merge($corpData, $configData);
    }
}
