<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Action\Dashboard\Group;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ChannelCode\Contract\ChannelCodeGroupContract;

/**
 * 渠道码分组列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var ChannelCodeGroupContract
     */
    private $channelCodeGroup;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/channelCodeGroup/index", methods="GET")
     */
    public function handle()
    {
        $res = $this->channelCodeGroup
            ->getChannelCodeGroupsByCorpId(user()['corpIds'], ['id', 'name']);

        if (empty($res)) {
            return [];
        }

        array_walk($res, function (&$item) {
            $item['groupId'] = $item['id'];

            unset($item['id']);
        });

        $data = [
            'name' => '未分组',
            'groupId' => 0,
        ];

        $res[] = $data;

        return $res;
    }
}
