<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use Psr\Container\ContainerInterface;

/**
 * Class RoomList.
 * @Controller
 */
class RoomList extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @RequestMapping(path="/dashboard/roomTagPull/roomList", methods="get")
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
        ## 查询数据
        return $this->handleData($user, $params);
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
     * 查询数据.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function handleData(array $user, array $params): array
    {
        $list = $this->workRoomService->getWorkRoomsByCorpIdOwnerIds($user['corpIds'][0], $params, ['id', 'wx_chat_id', 'name', 'owner_id', 'room_max']);
        foreach ($list as $k => $v) {
            $list[$k]['contact_num'] = $this->workContactRoomService->countWorkContactRoomsByRoomIdContact((int) $v['id']);
        }
        ## 会话内容存档
        if (isset($params['type']) && (int) $params['type'] === 2) {
            ##EasyWeChat获取会话内容存档开启成员列表
            $res = $this->wxApp($user['corpIds'][0], 'contact')->msg_audit->getPermitUsers();
            if ($res['errcode'] !== 0) {
                $this->logger->error(sprintf('获取会话内容存档开启成员列表失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            }
            $userList = $res['ids'];
            if (! empty($userList)) {
                foreach ($userList as $item) {
                    $this->workEmployeeService->updateWorkEmployeeByCorpIdWxUserId($user['corpIds'][0], $item, ['audit_status' => 1]);
                }
            }
            foreach ($list as $key => $val) {
                $list[$key]['audit_status'] = 0;
                $roomEmployee = $this->workContactRoomService->getWorkContactRoomsByRoomIdEmployee($val['id'], ['employee_id']);
                $audit = $this->workEmployeeService->getWorkEmployeesByIdAuditStatus(array_column($roomEmployee, 'employeeId'), 1);
                if (! empty($audit)) {
                    $list[$key]['audit_status'] = 1;
                }
            }
        }

        return $list;
    }
}
