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
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;
use Psr\Container\ContainerInterface;

/**
 * 标签建群-提醒发送
 * Class RemindSend.
 * @Controller
 */
class RemindSend extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var RoomTagPullContract
     */
    protected $roomTagPullService;

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
     * @var WorkAgentContract
     */
    private $workAgentService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

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
     * @RequestMapping(path="/dashboard/roomTagPull/remindSend", methods="get")
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
            'id.required' => '标签建群活动id 必传',
        ];
    }

    /**
     * @throws \JsonException
     */
    private function handleData(array $user, array $params): array
    {
        $roomTagPUll = $this->roomTagPullService->getRoomTagPullById((int) $params['id'], ['employees', 'choose_contact', 'contact_num', 'wx_tid', 'created_at']);
        $messageRemind = make(MessageRemind::class);
        foreach (json_decode($roomTagPUll['wxTid'], true, 512, JSON_THROW_ON_ERROR) as $item) {
            if (isset($params['wxUserId']) && $item['wxUserId'] !== $params['wxUserId']) {
                continue;
            }
            if ($item['status'] === 0) {
                $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserId($user['corpIds'][0], $item['wxUserId'], ['wx_user_id']);
                $contact = $this->workContactService->getWorkContactsByEmployeeIdSearch($user['corpIds'][0], (int) $item, json_decode($roomTagPUll['chooseContact'], true, 512, JSON_THROW_ON_ERROR));
                if (! empty($contact)) {
                    continue;
                }
                $content = "管理员提醒你发送群发任务\n任务创建于{$roomTagPUll['createdAt']},将群发给{$contact[0]['name']}等{$roomTagPUll['contactNum']}个客户，可前往【客户联系】中确认发送";
                $messageRemind->sendToEmployee(
                    (int) $user['corpIds'][0],
                    $employee['wxUserId'],
                    'text',
                    $content
                );
            }
        }
        return [];
    }
}
