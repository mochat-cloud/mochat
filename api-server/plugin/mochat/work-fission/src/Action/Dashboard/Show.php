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
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionInviteContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPosterContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;

/**
 * 企业微信授权- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkFissionPosterContract
     */
    protected $workFissionPosterService;

    /**
     * @Inject
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

    /**
     * @Inject
     * @var WorkFissionPushContract
     */
    protected $workFissionPushService;

    /**
     * @Inject
     * @var WorkFissionInviteContract
     */
    protected $workFissionInviteService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/workFission/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData($id);
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
            'id.required' => '任务宝ID 必填',
            'id.integer' => '任务宝ID 必需为整数',
            'id.min  ' => '任务宝ID 不可小于1',
        ];
    }

    private function handleData($id): array
    {
        $fission = $this->workFissionService->getWorkFissionById((int) $id);
        $posterInfo = $this->workFissionPosterService->getWorkFissionPosterByFissionId((int) $id);
        $welcomeInfo = $this->workFissionWelcomeService->getWorkFissionWelcomeByFissionId((int) $id);
        return [
            'id' => $fission['id'],
            'active_name' => $fission['activeName'],
            'qrcode_url' => $posterInfo['qrcodeUrl'],
            'link' => Url::getAuthRedirectUrl(7, (int) $id),
            'active_time' => $fission['createdAt'] . '-' . $fission['endTime'],
            'service_employees' => $fission['serviceEmployees'],
            'contact_tags' => $fission['contactTags'],
            'welcome_text' => $welcomeInfo['msgText'],
            'welcome_title' => $welcomeInfo['linkTitle'],
            'welcome_desc' => $welcomeInfo['linkDesc'],
            'welcome_url' => $welcomeInfo['linkCoverUrl'],
        ];
    }
}
