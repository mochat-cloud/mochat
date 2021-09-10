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
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\File;
use MoChat\App\Utils\Url;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionInviteContract;

/**
 * 任务宝 - 发送邀请信息.
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
     * 邀请用户.
     * @var WorkFissionInviteContract
     */
    protected $workFissionInviteService;

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

    public function __construct(\Hyperf\HttpServer\Contract\RequestInterface $request, CorpContract $corpService, WorkFissionInviteContract $workFissionInviteService, WorkContactEmployeeContract $contactEmployeeService, WorkContactContract $workContactService)
    {
        $this->request = $request;
        $this->corpService = $corpService;
        $this->workFissionInviteService = $workFissionInviteService;
        $this->contactEmployeeService = $contactEmployeeService;
        $this->workContactService = $workContactService;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workFission/invite", methods="post")
     * @throws \JsonException
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
        return $this->handleInvite($params['fission_id'], $data);
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
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ##邀请客户
        if (! empty($params['link_pic'])) {
            $params['link_pic'] = File::uploadBase64Image($params['link_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }

        $data = [
            'fission_id' => (int) $params['fission_id'],
            'employees' => empty($params['employees']) ? '{}' : json_encode($params['employees'], JSON_THROW_ON_ERROR),
            'choose_contact' => empty($params['choose_contact']) ? '{}' : json_encode($params['choose_contact'], JSON_THROW_ON_ERROR),
            'text' => $params['text'],
            'link_title' => $params['link_title'],
            'link_desc' => $params['link_desc'],
            'link_pic' => $params['link_pic'],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->sendMsg($user, (int) $params['fission_id'], $this->handleContact($params['filter']), $data);

        return $data;
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleContact($params): array
    {
        ##邀请客户
        $userList = [];
        $where = [];
        if (! empty($params['employee_ids'])) {
            $where[] = ['employee_id', 'in', json_decode($params['employee_ids'], true, 512, JSON_THROW_ON_ERROR)];
        }
        if ($params['is_all'] == 1) {
            if (! empty($params['start_time'])) {
                $where[] = ['create_time', '>', $params['start_time']];
                $where[] = ['create_time', '<', $params['end_time']];
            }
        }
        $user = $this->contactEmployeeService->getWorkContactEmployeeList($where, ['contact_id']);
        if ($params['is_all'] == 0 || (! isset($params['gender']) && ! empty($user['data']))) {
            $userList = $this->workContactService->getWorkContactsById(array_column($user['data'], 'contactId'), ['wx_external_userid']);
        }
        $whereContact = [];
        if (isset($params['gender']) && is_numeric($params['gender'] && ! empty($user['data']))) {
            $whereContact[] = ['id', 'in', array_column($user['data'], 'contactId')];
            $whereContact['gender'] = $params['gender'];
            $contact = $this->workContactService->getWorkContactList($whereContact, ['wx_external_userid']);
            $userList = $contact['data'];
        }
        if (empty($userList)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '没有客户');
        }
        return $userList;
    }

    /**
     * @param $fissionId
     * @param $params
     */
    private function handleInvite($fissionId, $params): array
    {
        $info = $this->workFissionInviteService->getWorkFissionInviteByFissionId((int) $fissionId);
        if (empty($info)) {
            $this->workFissionInviteService->createWorkFission($params);
        } else {
            $this->workFissionInviteService->updateWorkFissionById((int) $fissionId, $params);
        }
        return [];
    }

    /**
     * 创建企业群发.
     * @param $contact
     * @param $data
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendMsg(array $user, int $fissionId, $contact, $data): array
    {
        ##EasyWeChat添加企业群发消息模板.
        $easyWeChatParams['text']['content'] = $data['text'];
        $easyWeChatParams['link'] = ['title' => $data['link_title'], 'picurl' => $data['link_pic'], 'desc' => $data['link_desc'], 'url' => Url::getAuthRedirectUrl(7, $fissionId)];
        $easyWeChatParams['external_userid'] = array_column($contact, 'wxExternalUserid');
        $res = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message->submit($easyWeChatParams);
        if ($res['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '发送失败');
        }
        return [];
    }
}
