<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Medium;

use App\Action\ContactField\Traits\RequestTrait;
use App\Constants\Medium\IsSync;
use App\Constants\Medium\Type;
use App\Contract\MediumGroupServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
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
    use UserTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/medium/index", methods="GET")
     */
    public function handle(): array
    {
        ## 企业ID
        $corpId = $this->corpId();

        ## 参数验证
        $params = $this->request->inputs(
            ['searchStr', 'mediumGroupId', 'type', 'page', 'perPage'],
            ['type' => 0, 'page' => 1, 'perPage' => 10]
        );

        ## 模型查询
        $client                                                        = $this->container->get(MediumServiceInterface::class);
        $where                                                         = [];
        $where['is_sync']                                              = IsSync::SYNC;
        $where['corp_id']                                              = $corpId;
        $params['type'] && $where['type']                              = $params['type'];
        $params['mediumGroupId'] !== null && $where['medium_group_id'] = $params['mediumGroupId'];
        $params['searchStr'] && $where[]                               = ['content', 'LIKE', '%' . $params['searchStr'] . '%'];

        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => '`created_at` DESC',
        ];
        $pageData = $client->getMediumList(
            $where,
            ['id', 'type', 'media_id', 'content', 'corp_id', 'medium_group_id', 'user_id', 'user_name', 'created_at'],
            $options
        );

        ## 响应数据处理
        $groups = $this->container->get(MediumGroupServiceInterface::class)->getMediumGroupsById(array_column($pageData['data'], 'mediumGroupId'), ['id', 'name']);
        $groups = array_column($groups, 'name', 'id');
        $data   = array_map(function ($item) use ($groups, $client) {
            $item['content'] = $client->addFullPath(json_decode($item['content'], true), $item['type']);
            $item['mediumGroupName'] = $item['mediumGroupId'] ? $groups[$item['mediumGroupId']] : '未分组';
            $item['type'] = Type::getMessage($item['type']);
            return $item;
        }, $pageData['data']);

        return [
            'page' => [
                'perPage'   => $pageData['per_page'],
                'total'     => $pageData['total'],
                'totalPage' => $pageData['last_page'],
            ],
            'list' => $data,
        ];
    }
}
