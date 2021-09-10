<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Action\Sidebar;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;

/**
 * 企业微信-侧边栏-群日历列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * StoreLogic constructor.
     */
    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/roomCalendar/index", methods="get")
     * @throws \JsonException
     */
    public function handle()
    {
        $params['corpId'] = (int) $this->request->input('corpId');  //企业 id
        ## 验证接受参数
        $this->validated($params);

        ## 列表
        return $this->roomCalendarService->getRoomCalendarByCorpId($params['corpId'], ['id', 'name']);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => 'corpId 必传',
        ];
    }
}
