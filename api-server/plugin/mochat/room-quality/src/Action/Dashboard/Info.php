<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use Psr\Container\ContainerInterface;

/**
 * 群聊质检-详情
 * Class Store.
 * @Controller
 */
class Info extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

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
     * @RequestMapping(path="/dashboard/roomQuality/info", methods="get")
     * @throws \JsonException
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
        ## 详情
        $info = $this->roomQualityService->getRoomQualityById((int) $params['id'], ['name', 'rooms', 'quality_type', 'work_cycle', 'rule', 'white_list_status', 'keyword', 'status', 'create_user_id', 'created_at']);
        $info['rooms'] = json_decode($info['rooms'], true, 512, JSON_THROW_ON_ERROR);
        $info['workCycle'] = json_decode($info['workCycle'], true, 512, JSON_THROW_ON_ERROR);
        $rule = json_decode($info['rule'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($rule as $k => $v) {
            if ((int) $v['employee_type'] === 1 && ! empty($v['employee'])) {
                foreach ($v['employee'] as $key => $item) {
                    $emp = $this->workEmployeeService->getWorkEmployeeById($item, ['name', 'avatar']);
                    $rule[$k]['employee'][$key] = ['id' => $item, 'name' => $emp['name'], 'avatar' => file_full_url($emp['avatar'])];
                }
            }
        }
        $info['rule'] = $rule;
        $info['keyword'] = empty($info['keyword']) ? '' : explode(',', $info['keyword']);
        ## 处理创建者信息
        $username = $this->userService->getUserById($info['createUserId']);
        $info['createUserName'] = isset($username['name']) ? $username['name'] : '';
        return $info;
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
            'id.required' => 'id 必传',
        ];
    }
}
