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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityContract;
use Psr\Container\ContainerInterface;

/**
 * 企业微信-侧边栏-群聊质检列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomQualityContract
     */
    protected $roomQualityService;

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
     * @RequestMapping(path="/sidebar/roomQuality/index", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $params['corpId'] = (int) $this->request->input('corpId');  //企业 id
        $params['roomId'] = $this->request->input('roomId');       //群聊 id
        ## 参数验证
        $this->validated($params);
        ## 列表查询
        $quality = $this->roomQualityService->getRoomQualityByCorpIdRoomId($params['corpId'], $params['roomId'], ['name', 'rule']);
        if (! empty($quality)) {
            foreach ($quality as $k => $v) {
                $quality[$k]['rule'] = json_decode($v['rule'], true, 512, JSON_THROW_ON_ERROR);
            }
        }
        $list = $this->roomQualityService->getRoomQualityByCorpId($params['corpId'], ['id', 'name']);
        return ['list' => $list, 'roomQuality' => $quality];
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
        ];
    }
}
