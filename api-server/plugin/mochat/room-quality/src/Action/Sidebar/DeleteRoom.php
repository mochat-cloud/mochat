<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomQuality\Action\Sidebar;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use Psr\Container\ContainerInterface;

/**
 * 企业微信-侧边栏-群聊质检移除群聊.
 * @Controller
 */
class DeleteRoom extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

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
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/roomQuality/deleteRoom", methods="get")
     * @throws \JsonException
     */
    public function handle()
    {
        $params['corpId'] = (int) $this->request->input('corpId');  //企业 id
        $params['roomId'] = $this->request->input('roomId');       //群聊 id
        $params['id'] = (int) $this->request->input('id');          //活动 id
        ## 参数验证
        $this->validated($params);
        ## 群日历详情
        $info = $this->roomQualityService->getRoomQualityById($params['id'], ['id', 'rooms']);
        $rooms = empty($info['rooms']) ? [] : json_decode($info['rooms'], true, 512, JSON_THROW_ON_ERROR);
        if (! empty($rooms)) {
            foreach ($rooms as $k => $v) {
                if ($v['wxChatId'] === $params['roomId']) {
                    unset($rooms[$k]);
                }
            }
            $rooms = array_merge($rooms);
        }
        ## 设置群聊
        $this->roomQualityService->updateRoomQualityById($params['id'], ['rooms' => json_encode($rooms, JSON_THROW_ON_ERROR)]);
        return [];
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
            'roomId' => 'required',
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
            'corpId.required' => 'corpId 必传',
            'roomId.required' => 'roomId 必传',
            'id.required' => 'id 必传',
        ];
    }
}
