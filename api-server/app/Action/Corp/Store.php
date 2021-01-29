<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Corp;

use App\Action\Corp\Traits\RequestTrait;
use App\Contract\CorpServiceInterface;
use App\Contract\TenantServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use App\Middleware\PermissionMiddleware;
use App\QueueService\WorkEmployee\EmployeeApply;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\ApplicationContext;
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
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var TenantServiceInterface
     */
    protected $tenantClient;

    /**
     * @Inject
     * @var WorkMessageConfigServiceInterface
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
     * @RequestMapping(path="/corp/store", methods="post")
     * @Middleware(PermissionMiddleware::class)
     * @throws \Exception 异常处理
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 接收参数
        $params = [
            'name'            => $this->request->input('corpName'),
            'wx_corpid'       => $this->request->input('wxCorpId'),
            'employee_secret' => $this->request->input('employeeSecret'),
            'contact_secret'  => $this->request->input('contactSecret'),
        ];
        ## 参数验证
        $this->validated($this->request->all(), 'store');

        $params['event_callback'] = config('framework.app_domain') . config('framework.wework.callback_path');

        ## 回调token
        $tokenKey        = random_bytes(random_int(1, 24));
        $params['token'] = str_replace(['/', '+', '='], '', base64_encode($tokenKey));
        ## 回调消息加密串
        $params['encoding_aes_key'] = $this->getAesKeyStr();

        $params['created_at'] = date('Y-m-d H:i:s');
        $params['tenant_id']  = user('tenantId');

        ## 数据操作
        Db::beginTransaction();
        try {
            ## 企业授信
            $corpId = $this->corpService->createCorp($params);
            ## 会话存档-配置
            $this->workMessageConfigService->createWorkMessageConfig(['corp_id' => $corpId, 'chat_apply_status' => 3, 'created_at' => date('Y-m-d H:i:s')]);
            ## 绑定用户与企业的关系
            $container = ApplicationContext::getContainer();
            $redis     = $container->get(\Hyperf\Redis\Redis::class);
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
        $keyStrBase64 = '';
        while (strlen($keyStrBase64) !== 43) {
            $keyStr       = random_bytes(32);
            $keyStrBase64 = str_replace(['/', '+', '='], '', base64_encode($keyStr));
        }
        return $keyStrBase64;
    }
}
