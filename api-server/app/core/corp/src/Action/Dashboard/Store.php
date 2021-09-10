<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Corp\Action\Dashboard;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\ApplicationContext;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Action\Dashboard\Traits\RequestTrait;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Tenant\Contract\TenantContract;
use MoChat\App\Utils\Url;
use MoChat\App\WorkEmployee\QueueService\EmployeeApply;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 企业微信授权- 创建提交.
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var TenantContract
     */
    protected $tenantClient;

    /**
     * @Inject
     * @var WorkMessageConfigContract
     */
    protected $workMessageConfigService;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/corp/store", methods="post")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \Exception 异常处理
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 接收参数
        $params = [
            'name' => trim($this->request->input('corpName')),
            'wx_corpid' => trim($this->request->input('wxCorpId')),
            'employee_secret' => trim($this->request->input('employeeSecret')),
            'contact_secret' => trim($this->request->input('contactSecret')),
        ];
        ## 参数验证
        $this->validated($this->request->all(), 'store');

        $params['event_callback'] = Url::getApiBaseUrl() . '/' . ltrim(config('framework.wework.callback_path'), '/');

        ## 回调token
        $tokenKey = random_bytes(random_int(1, 24));
        $params['token'] = str_replace(['/', '+', '='], '', base64_encode($tokenKey));
        ## 回调消息加密串
        $params['encoding_aes_key'] = $this->getAesKeyStr();

        $params['created_at'] = date('Y-m-d H:i:s');
        $params['tenant_id'] = user('tenantId');

        ## 数据操作
        Db::beginTransaction();
        try {
            ## 企业授信
            $corpId = $this->corpService->createCorp($params);
            ## 会话存档-配置
            $this->workMessageConfigService->createWorkMessageConfig(['corp_id' => $corpId, 'chat_apply_status' => 3, 'created_at' => date('Y-m-d H:i:s')]);
            ## 绑定用户与企业的关系
            $container = ApplicationContext::getContainer();
            $redis = $container->get(\Hyperf\Redis\Redis::class);
            $redis->set('mc:user.' . user()['id'], $corpId . '-0');

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '企业微信授信创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '企业微信授信创建失败');
        }

        ## 同步企业通讯录信息
        make(EmployeeApply::class)->handle([$corpId]);

        return [];
    }

    /**
     * 生成43位aesKey的base64字符串.
     */
    private function getAesKeyStr(): string
    {
        $baseChars = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        ];
        shuffle($baseChars);

        return implode('', array_slice($baseChars, 0, 43));
    }
}
