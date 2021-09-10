<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\Codec\Json;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\File;
use MoChat\App\Utils\Url;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionInviteContract;

/**
 * 群裂变 - 发送邀请信息.
 *
 * Class Invite.
 * @Controller
 */
class Invite extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @var \Hyperf\HttpServer\Contract\RequestInterface
     */
    protected $request;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 邀请用户.
     * @Inject
     * @var RoomFissionInviteContract
     */
    protected $roomFissionInviteService;

    /**
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

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
     * Invite constructor.
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
     * @RequestMapping(path="/dashboard/roomFission/invite", methods="post")
     * @throws \Exception
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 参数验证
        $params = $this->request->all();
        ## 处理参数
        $data = $this->handleParam($user, $params);
        return $this->handleInvite((int) $params['id'], $data);
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
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \Exception
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ##邀请客户
        if (! empty($params['link_pic'])) {
            $params['link_pic'] = File::uploadBase64Image($params['link_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }

        $data = [
            'type' => (int) $params['type'],
            'employees' => empty($params['employees']) ? '{}' : json_encode($params['employees'], JSON_THROW_ON_ERROR),
            'choose_contact' => empty($params['choose_contact']) ? '{}' : json_encode($params['choose_contact'], JSON_THROW_ON_ERROR),
            'text' => $params['text'],
            'link_title' => $params['link_title'],
            'link_desc' => $params['link_desc'],
            'link_pic' => $params['link_pic'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        ## 发送微信邀请
        if ((int) $params['type'] === 1) {
            ##EasyWeChat添加企业群发消息模板.
            $wx = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message;
            foreach ($params['employees'] as $employeeId) {
                $contact = $this->workContactService->getWorkContactsBySearch($user['corpIds'][0], [$employeeId], $params['choose_contact']);
                $employee = $this->workEmployeeService->getWorkEmployeeById((int) $employeeId, ['wx_user_id']);

                $easyWeChatParams['text']['content'] = $data['text'];
                $easyWeChatParams['link'] = ['title' => $data['link_title'], 'picurl' => $data['link_pic'], 'desc' => $data['link_desc'], 'url' => Url::getAuthRedirectUrl(8, (int) $params['id'], [
                    'parent_union_id' => '',
                    'wx_user_id' => $employee['wxUserId'],
                ])];
                $easyWeChatParams['external_userid'] = array_column($contact, 'wxExternalUserid');
                $easyWeChatParams['sender'] = $employee['wxUserId'];
                $res = $wx->submit($easyWeChatParams);
                if ($res['errcode'] !== 0) {
                    $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '发送群裂变邀请通知消息失败', date('Y-m-d H:i:s'), Json::encode($easyWeChatParams), Json::encode($res)));
                }
            }
        }
        return $data;
    }

    private function handleInvite(int $id, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            $this->roomFissionInviteService->updateRoomFissionInviteById($id, $params);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '邀请客户失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [];
    }
}
