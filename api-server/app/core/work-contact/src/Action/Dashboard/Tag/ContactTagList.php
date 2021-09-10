<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Dashboard\Tag;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagGroupContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use Psr\Container\ContainerInterface;

/**
 * 客户标签列表.
 *
 * Class ContactTagList
 * @Controller
 */
class ContactTagList extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkContactTagGroupContract
     */
    protected $workContactTagGroupService;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    private $contactTagService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workContactTag/contactTagList", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        // 获取标签组
        $group = $this->workContactTagGroupService->getWorkContactTagGroupsByCorpId(user()['corpIds'], ['id', 'wx_group_id', 'group_name']);

        //获取标签列表
        foreach ($group as $k => $v) {
            $tags = $this->contactTagService->getWorkContactTagsByGroupIds([$v['id']], ['id', 'wx_contact_tag_id', 'name', 'contact_tag_group_id']);
            if (isset($params['name']) && ! empty($params['name'])) {
                $tags = $this->contactTagService->getWorkContactTagsByGroupIdsName([$v['id']], $params['name'], ['id', 'wx_contact_tag_id', 'name', 'contact_tag_group_id']);
            }
            $group[$k]['tags'] = $tags;
        }
        return $group;
    }
}
