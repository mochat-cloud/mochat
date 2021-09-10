<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarChannelContract;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;

/**
 * 互动雷达-渠道链接列表.
 *
 * Class Index.
 * @Controller
 */
class IndexChannelLink extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var RadarChannelContract
     */
    private $radarChannelService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/radar/indexChannelLink", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 验证参数
        $params = $this->request->all();
        $this->validated($this->request->all());

        return $this->getRadarChannelList($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'radar_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'radar_id.required' => '雷达id 必传',
        ];
    }

    /**
     * 获取渠道列表.
     * @return array[]
     */
    private function getRadarChannelList(array $user, array $params): array
    {
        $list = $this->radarChannelLinkService->getRadarChannelLinkByCorpIdRadarId($user['corpIds'][0], (int) $params['radar_id'], ['id', 'radar_id', 'channel_id', 'link', 'click_num', 'click_person_num', 'create_user_id', 'created_at']);
        foreach ($list as $k => $v) {
            $channel = $this->radarChannelService->getRadarChannelById($v['channelId'], ['name']);
            $list[$k]['name'] = $channel['name'];
            $username = $this->userService->getUserById($v['createUserId']);
            $list[$k]['link'] = $v['link'];
            $list[$k]['create_user'] = isset($username['name']) ? $username['name'] : '';
            unset($list[$k]['id'], $list[$k]['radarId'], $list[$k]['channelId'], $list[$k]['createUserId']);
        }

        return ['list' => $list];
    }
}
