<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkDepartment;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use App\Logic\WorkDepartment\PageIndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
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
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkMessageConfigServiceInterface
     */
    protected $workMessageConfigService;

    /**
     * @Inject
     * @var PageIndexLogic
     */
    private $pageIndexLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workDepartment/pageIndex", methods="get")
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
            'name'       => $this->request->input('name', null),
            'parentName' => $this->request->input('parentName', null),
            'page'       => $this->request->input('page', 1),
            'perPage'    => $this->request->input('perPage', 10),
        ];
        return $this->pageIndexLogic->handle($user, $params);
    }
}
