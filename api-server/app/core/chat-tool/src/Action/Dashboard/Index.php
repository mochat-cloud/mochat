<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\ChatTool\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\ChatTool\Constants\Status;
use MoChat\App\ChatTool\Contract\ChatToolContract;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 聊天栏列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    protected $workAgentService;

    /**
     * @Inject
     * @var ChatToolContract
     */
    protected $chatToolService;

    /**
     * @api(
     *      #apiRoute /chatTool/config
     *      #apiTitle 配置详情
     *      #apiMethod GET
     *      #apiName GetChatToolConfig
     *      #apiDescription
     *      #apiGroup 侧边栏-JSSDK
     *      #apiSuccess {String[]} agents 应用
     *      #apiSuccess {Number} agents.id 应用ID
     *      #apiSuccess {String} agents.name 应用名称
     *      #apiSuccess {String} agents.squareLogoUrl 企业应用方形头像
     *      #apiSuccess {String[]} agents.chatTools 应用侧边栏
     *      #apiSuccess {String} agents.chatTools.pageName 应用侧边栏-页面名称
     *      #apiSuccess {String} agents.chatTools.pageUrl 应用侧边栏-页面URL
     *      #apiSuccess {String[]} whiteDomains 可信域名
     *      #apiSuccessExample {json} Success-Response:
     *          {
     *              "agents": [
     *                {
     *                    "id": 1,
     *                    "name": "应用名称",
     *                   "squareLogoUrl": "企业应用方形头像",
     *                   chatTools: [
     *                        {
     *                            "pageName": "页面名称",
     *                            "pageUrl": "页面URL"
     *                        }
     *                   ],
     *                },
     *              ],
     *              "whiteDomains": ["http://xx.com", "http://yy.com"]
     *          }
     *      #apiErrorExample {json} Error-Response:
     *      {
     *        "code": "100014",
     *        "msg": "服务异常",
     *        "data": []
     *      }
     * )
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/chatTool/config", methods="GET")
     */
    public function handle(): array
    {
        $corpId = (int) user('corpIds')[0];

        ## 应用信息
        $agents = $this->workAgentService->getWorkAgentByCorpIdClose($corpId, ['id', 'corp_id', 'name', 'square_logo_url']);
        if (empty($agents)) {
            return [];
        }

        ## 侧边栏信息
        $chatTools = $this->chatToolService->getChatToolsByStatus(Status::STATUS_YES, ['id', 'page_name', 'page_flag']);

        $domain = Url::getSidebarBaseUrl();
        $newChatTools = static function ($agentId) use ($chatTools, $domain) {
            return array_map(static function ($item) use ($domain, $agentId) {
                if ($item['pageFlag'] === 'customer') {
                    $item['pageFlag'] = 'contact';
                }

                if ($item['pageFlag'] === 'mediumGroup') {
                    $item['pageFlag'] = 'medium';
                }

                $item['pageUrl'] = $domain . "/{$item['pageFlag']}?" . http_build_query([
                    'agentId' => $agentId,
                ]);
                return $item;
            }, $chatTools);
        };

        $newAgents = array_map(static function ($item) use ($newChatTools) {
            $item['chatTools'] = $newChatTools($item['id']);
            return $item;
        }, $agents);

        return [
            'agents' => $newAgents,
            'whiteDomains' => [Url::getSidebarBaseUrl(), Url::getApiBaseUrl()],
        ];
    }
}
