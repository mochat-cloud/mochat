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
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 任务宝 - 选择客户数量.
 *
 * Class Invite.
 * @Controller
 */
class ChooseContact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Hyperf\HttpServer\Contract\RequestInterface
     */
    protected $request;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * 邀请用户.
     * @var WorkContactEmployeeContract
     */
    protected $contactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * ChooseContact constructor.
     */
    public function __construct(\Hyperf\HttpServer\Contract\RequestInterface $request, CorpContract $corpService, WorkContactEmployeeContract $contactEmployeeService, WorkContactContract $workContactService)
    {
        $this->request = $request;
        $this->corpService = $corpService;
        $this->contactEmployeeService = $contactEmployeeService;
        $this->workContactService = $workContactService;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workFission/chooseContact", methods="get")
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
        $this->validated($params, 'store');
        return $this->handleInvite($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * @param $params
     */
    private function handleInvite($params): array
    {
        ##邀请客户
        $where = [];
        if (! empty($params['employee_ids'])) {
            $where[] = ['employee_id', 'in', json_decode($params['employee_ids'], true)];
        }
        if ($params['is_all'] == 1) {
            if (! empty($params['start_time'])) {
                $where[] = ['create_time', '>', $params['start_time']];
                $where[] = ['create_time', '<', $params['end_time']];
            }
        }
        $user = $this->contactEmployeeService->getWorkContactEmployeeList($where, ['contact_id']);
        if ($params['is_all'] == 0 || ! isset($params['gender'])) {
            return [$this->workContactService->countWorkContactsById(array_column($user['data'], 'contactId'))];
        }
        if (isset($params['gender']) && is_numeric($params['gender'] && ! empty($user['data']))) {
            return [$this->workContactService->countWorkContactsByIdGender(array_column($user['data'], 'contactId'), (int) $params['gender'])];
        }
        return [0];
    }
}
