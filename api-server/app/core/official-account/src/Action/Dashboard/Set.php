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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use Psr\Container\ContainerInterface;

/**
 * 公众号-设置
 * Class OfficialAccountSet.
 * @Controller
 */
class Set extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var OfficialAccountSetContract
     */
    protected $officialAccountSetService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/officialAccount/set", methods="get")
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 接收参数
        $params = [
            'type' => (int) $this->request->input('type'),
            'official_account_id' => (int) $this->request->input('official_account_id', null),
        ];
        return $this->getOfficialAccount($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required',
            'official_account_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => 'type 必传',
            'official_account_id.required' => 'official_account_id 必传',
        ];
    }

    /**
     * 数据库操作.
     * @return array 响应数组
     */
    private function getOfficialAccount(array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            $info = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($user['corpIds'][0], $params['type'], ['id', 'official_account_id', 'type', 'corp_id']);
            if (empty($info)) {
                ## 创建活动
                $params['corp_id'] = $user['corpIds'][0];
                $params['tenant_id'] = 0;
                $params['create_user_id'] = $user['id'];
                $params['created_at'] = date('Y-m-d H:i:s');
                $this->officialAccountSetService->createOfficialAccountSet($params);
            } else {
                $this->officialAccountSetService->updateOfficialAccountSetById($info['id'], $params);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '公众号设置失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [];
    }
}
