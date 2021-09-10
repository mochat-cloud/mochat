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
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarChannelContract;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;

/**
 * 互动雷达-渠道列表.
 *
 * Class Index.
 * @Controller
 */
class IndexChannel extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

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
     * @RequestMapping(path="/dashboard/radar/indexChannel", methods="get")
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
        $list = $this->radarChannelService->getRadarChannelByCorpId($user['corpIds'][0], ['id', 'name']);
//        foreach ($list as $k => $v) {
//            $list[$k]['status'] = 0;
//            $link = $this->radarChannelLinkService->getRadarChannelLinkByCorpIdRadarIdChannelId($user['corpIds'][0], (int)$params['radar_id'], $v['id'], ['id']);
//            if (!empty($link)) $list[$k]['status'] = 1;
//        }

        return ['list' => $list];
    }
}
