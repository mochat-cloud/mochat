<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkDepartment\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkDepartment\Logic\PageIndexLogic;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 组织架构-部门列表.
 *
 * Class PageIndex.
 * @Controller
 */
class PageIndex extends AbstractAction
{
    //use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkMessageConfigContract
     */
    protected $workMessageConfigService;

    /**
     * @Inject
     * @var PageIndexLogic
     */
    private $pageIndexLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workDepartment/pageIndex", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $params = [
            'name' => $this->request->input('name', null),
            'parentName' => $this->request->input('parentName', null),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10),
        ];
        return $this->pageIndexLogic->handle($user, $params);
    }
}
