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
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Contract\OfficialAccountSetContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use Psr\Container\ContainerInterface;

/**
 * 公众号-列表
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var OfficialAccountContract
     */
    protected $officialAccountService;

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
     * @RequestMapping(path="/dashboard/officialAccount/index", methods="get")
     */
    public function handle(): array
    {
        $user = user();
        // 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $param = $this->request->all();
        // 查询全部
        if (empty($param['type'])) {
            return $this->getOfficialAccount($user['corpIds'][0]);
        }
        // 模块查询
        $set = $this->officialAccountSetService->getOfficialAccountSetByCorpIdType($user['corpIds'][0], (int) $param['type'], ['official_account_id']);
        if (! empty($set)) {
            $info = $this->officialAccountService->getOfficialAccountById($set['officialAccountId'], ['id', 'nickname', 'avatar']);
            if (empty($info)) {
                return [];
            }
            $info['avatar'] = ! empty($info['avatar']) ? file_full_url($info['avatar']) : '';
            return $info;
        }
        // 模块查询 不存在 创建
        if (empty($set)) {
            $officialAccount = $this->officialAccountService->getOfficialAccountByCorpId($user['corpIds'][0], ['id', 'nickname', 'avatar']);
            if (empty($officialAccount)) {
                return [];
            }
            $data = [
                'type' => (int) $param['type'],
                'official_account_id' => $officialAccount['id'],
                'corp_id' => $user['corpIds'][0],
                'tenant_id' => 0,
                'create_user_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->officialAccountSetService->createOfficialAccountSet($data);
            $officialAccount['avatar'] = file_full_url((string) $officialAccount['avatar']);
            return $officialAccount;
        }
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
        ];
    }

    /**
     * 获取列表.
     * @return array 响应数组
     */
    private function getOfficialAccount(int $corpId): array
    {
        $list = $this->officialAccountService->getOfficialAccountsByCorpId($corpId, ['id', 'nickname', 'avatar']);
        foreach ($list as $k => $v) {
            $list[$k]['avatar'] = file_full_url($v['avatar']);
        }
        return $list;
    }
}
