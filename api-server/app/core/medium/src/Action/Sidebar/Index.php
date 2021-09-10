<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Sidebar;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\Medium\Action\Dashboard\Traits\RequestTrait;
use MoChat\App\Medium\Constants\IsSync;
use MoChat\App\Medium\Constants\Type;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Medium\Contract\MediumGroupContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/medium/index", methods="GET")
     */
    public function handle(): array
    {
        ## 企业ID
        $corpId = (int) user()['corpId'];

        ## 参数验证
        $params = $this->request->inputs(
            ['searchStr', 'mediumGroupId', 'type', 'page', 'perPage'],
            ['type' => 0, 'page' => 1, 'perPage' => 10]
        );

        ## 模型查询
        $client = $this->container->get(MediumContract::class);
        $where = [];
        $where['is_sync'] = IsSync::SYNC;
        $where['corp_id'] = $corpId;
        $params['type'] && $where['type'] = $params['type'];
        $params['mediumGroupId'] !== null && $where['medium_group_id'] = $params['mediumGroupId'];
        $params['searchStr'] && $where[] = ['content', 'LIKE', '%' . $params['searchStr'] . '%'];

        $options = [
            'page' => $params['page'],
            'perPage' => $params['perPage'],
            'orderByRaw' => '`created_at` DESC',
        ];
        $pageData = $client->getMediumList(
            $where,
            ['id', 'type', 'media_id', 'content', 'corp_id', 'medium_group_id', 'user_id', 'user_name', 'created_at'],
            $options
        );

        ## 响应数据处理
        $groups = $this->container->get(MediumGroupContract::class)->getMediumGroupsById(array_column($pageData['data'], 'mediumGroupId'), ['id', 'name']);
        $groups = array_column($groups, 'name', 'id');
        $data = array_map(function ($item) use ($groups, $client) {
            $item['content'] = $client->addFullPath(json_decode($item['content'], true), $item['type']);
            $item['mediumGroupName'] = $item['mediumGroupId'] ? $groups[$item['mediumGroupId']] : '未分组';
            $item['type'] = Type::getMessage($item['type']);
            return $item;
        }, $pageData['data']);

        return [
            'page' => [
                'perPage' => $pageData['per_page'],
                'total' => $pageData['total'],
                'totalPage' => $pageData['last_page'],
            ],
            'list' => $data,
        ];
    }
}
