<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;

/**
 * 任务宝 - 获取客户邀请数据.
 *
 * Class Store.
 * @Controller
 */
class InviteDetail extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * Statistics constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService, WorkContactContract $workContactService)
    {
        $this->request = $request;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
        $this->workContactService = $workContactService;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workFission/inviteDetail", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        return $this->getUserList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '客户id 必填',
        ];
    }

    private function getUserList($params): array
    {
        ##邀请客户信息
        $columns = ['id', 'nickname', 'union_id', 'loss', 'created_at'];
        $userList = $this->workFissionContactService->getWorkFissionContactList(['contact_superior_user_parent' => $params['id']], $columns);
        foreach ($userList['data'] as $key => $val) {
            $user = $this->workContactService->getWorkContactByUnionId($val['unionId'], ['avatar']);
            $userList['data'][$key]['avatar'] = file_full_url($user['avatar']);
            unset($userList['data'][$key]['unionId']);
        }
        ##邀请客户数量
        $info = $this->workFissionContactService->getWorkFissionContactById((int) $params['id']);
        $new = $this->workFissionContactService->countWorkFissionContactNewByParent((int) $params['id']);
        $loss = $this->workFissionContactService->countWorkFissionContactLossByParent((int) $params['id']);
        return [
            'total_count' => empty($info['inviteCount']) ? 0 : $info['inviteCount'],
            'new_count' => $new,
            'loss' => $loss,
            'insert' => empty($info['inviteCount']) ? 0 : $info['inviteCount'] - $loss,
            'user_list' => $userList['data'],
        ];
    }
}
