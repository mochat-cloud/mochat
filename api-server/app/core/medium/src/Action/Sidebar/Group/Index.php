<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Sidebar\Group;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\Medium\Contract\MediumGroupContract;
use MoChat\Framework\Action\AbstractAction;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/mediumGroup/index", methods="GET")
     */
    public function handle(): array
    {
        ## 企业ID
        $corpId = (int) user()['corpId'];

        $client = $this->container->get(MediumGroupContract::class);
        $data = $client->getMediumGroupsByCorpId($corpId, ['id', 'name']);

        array_unshift($data, ['id' => 0, 'name' => '未分组']);
        return $data;
    }
}
