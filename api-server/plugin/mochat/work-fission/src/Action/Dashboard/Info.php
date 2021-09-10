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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Laminas\Stdlib\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionInviteContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPosterContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;
use MoChat\Plugin\WorkFission\Service\WorkFissionInviteService;

/**
 * 企业微信授权- 详情.
 *
 * Class Show.
 * @Controller
 */
class Info extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * 海报.
     * @var WorkFissionPosterContract
     */
    protected $workFissionPosterService;

    /**
     * 欢迎语.
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

    /**
     * 推送
     * @var WorkFissionPushContract
     */
    protected $workFissionPushService;

    /**
     * 邀请用户.
     * @var WorkFissionInviteContract
     */
    protected $workFissionInviteService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(\Hyperf\HttpServer\Contract\RequestInterface $request, WorkFissionContract $workFissionService, WorkFissionPosterContract $fissionPosterService, WorkFissionWelcomeContract $fissionWelcomeService, WorkFissionPushContract $fissionPushService, WorkFissionInviteService $workFissionInviteService)
    {
        $this->request = $request;
        $this->workFissionService = $workFissionService;
        $this->workFissionPosterService = $fissionPosterService;
        $this->workFissionWelcomeService = $fissionWelcomeService;
        $this->workFissionPushService = $fissionPushService;
        $this->workFissionInviteService = $workFissionInviteService;
    }

    /**
     * @RequestMapping(path="/dashboard/workFission/info", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleDate($id);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动ID 必填',
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
        ];
    }

    /**
     * @param $id
     * @throws \JsonException
     */
    private function handleDate($id): array
    {
        $res = [];
        $fission = $this->workFissionService->getWorkFissionById((int) $id);
        $poster = $this->workFissionPosterService->getWorkFissionPosterByFissionId((int) $id);
        $welcome = $this->workFissionWelcomeService->getWorkFissionWelcomeByFissionId((int) $id);
        $push = $this->workFissionPushService->getWorkFissionPushByFissionId((int) $id);
        $invite = $this->workFissionInviteService->getWorkFissionInviteByFissionId((int) $id);
        $res['fission'] = [
            'id' => $fission['id'],
            'active_name' => $fission['activeName'],
            'service_employees' => $fission['serviceEmployees'],
            'auto_pass' => $fission['autoPass'] == 1 ? 'true' : 'false',
            'auto_add_tag' => $fission['autoAddTag'] == 1 ? 'true' : 'false',
            'contact_tags' => $fission['contactTags'],
            'end_time' => $fission['endTime'],
            'qr_code_invalid' => $fission['qrCodeInvalid'],
            'tasks' => $fission['tasks'],
            'new_friend' => $fission['newFriend'] == 1 ? 'true' : 'false',
            'delete_invalid' => $fission['deleteInvalid'] == 1 ? 'true' : 'false',
            'receive_prize' => $fission['receivePrize'],
            'receive_prize_employees' => $fission['receivePrizeEmployees'],
            'receive_links' => $fission['receiveLinks'],
        ];
        $res['welcome'] = [
            'msg_text' => $welcome['msgText'],
            'link_title' => $welcome['linkTitle'],
            'link_desc' => $welcome['linkDesc'],
            'link_cover_url' => file_full_url($welcome['linkCoverUrl']),
        ];
        $res['poster'] = [
            'poster_type' => $poster['posterType'],
            'cover_pic' => file_full_url($poster['coverPic']),
            'wx_cover_pic' => $poster['wxCoverPic'],
            'foward_text' => $poster['fowardText'],
            'avatar_show' => $poster['avatarShow'] == 1 ? 'true' : 'false',
            'nickname_show' => $poster['nicknameShow'] == 1 ? 'true' : 'false',
            'nickname_color' => $poster['nicknameColor'],
            'card_corp_image_name' => $poster['cardCorpImageName'],
            'card_corp_name' => $poster['cardCorpName'],
            'card_corp_logo' => $poster['cardCorpLogo'],
            'qrcode_w' => $poster['qrcodeW'],
            'qrcode_h' => $poster['qrcodeH'],
            'qrcode_x' => $poster['qrcodeX'],
            'qrcode_y' => $poster['qrcodeY'],
            'qrcode_id' => $poster['qrcodeId'],
            'qrcode_url' => $poster['qrcodeUrl'],
        ];
        $msgComplex = '';
        if (! empty($push['msgComplex'])) {
            $msgComplex = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            if (! empty($msgComplex)) {
                $msgComplex['image'] = file_full_url($msgComplex['image']);
            }
        }
        $res['push'] = [
            'push_employee' => $push['pushEmployee'] == 1 ? 'true' : 'false',
            'push_contact' => $push['pushContact'] == 1 ? 'true' : 'false',
            'msg_text' => $push['msgText'],
            'msg_complex' => $msgComplex,
            'msg_complex_type' => $push['msgComplexType'],
        ];
        $res['invite'] = [
            'text' => $invite['text'], //待完善
            'link_title' => $invite['linkTitle'],
            'link_desc' => $invite['linkDesc'],
            'link_pic' => file_full_url($invite['linkPic']),
        ];
        return $res;
    }
}
