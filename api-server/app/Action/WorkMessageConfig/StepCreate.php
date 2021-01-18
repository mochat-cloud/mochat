<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessageConfig;

use App\Contract\CorpServiceInterface;
use App\Contract\TenantServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 微信配置(步骤) - 添加页面.
 * @Controller
 */
class StepCreate extends AbstractAction
{
    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workMessageConfig/stepCreate", methods="GET")
     */
    public function handle(): array
    {
        ## 当前企业
        $corpIds = user('corpIds');
        if (count($corpIds) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请选择一个企业，再进行操作');
        }
        $corpId = $corpIds[0];

        ## 企业基本信息
        $corpClient = $this->container->get(CorpServiceInterface::class);
        $corpData   = $corpClient->getCorpById($corpId, ['id', 'name', 'wx_corpid']);
        $data       = [
            'id'              => 0,
            'chatApplyStatus' => 0,
            'corpName'        => $corpData['name'],
            'wxCorpId'        => $corpData['wxCorpid'],
        ];

        ## 配置信息
        $corpConfigClient = $this->container->get(WorkMessageConfigServiceInterface::class);
        $corpConfigData   = $corpConfigClient->getWorkMessageConfigByCorpId($corpId, [
            'id', 'chat_apply_status', 'chat_rsa_key',
        ]);

        ## 第一步
        if (empty($corpConfigData)) {
            return $data;
        }
        $data = array_merge($data, $corpConfigData);

        ## 第二步
        if ($corpConfigData['chatApplyStatus'] === 2) {
            $data['serviceContactUrl'] = $this->config->get('framework.work_message_config.serviceContactUrl');
        }

        ## 第三步
        if ($corpConfigData['chatApplyStatus'] === 3) {
            $tenantData = $this->container->get(TenantServiceInterface::class)->getTenantByStatus();
            if (isset($tenantData['serverIps']) && $tenantData['serverIps']) {
                $data['chatWhitelistIp'] = json_decode($tenantData['serverIps'], true);
            } else {
                $data['chatWhitelistIp'] = [];
            }
            [$data['rsaPrivateKey'], $data['rsaPublicKey']] = rsa_keys();
        }

        return $data;
    }
}
