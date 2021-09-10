<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Index\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Index\Logic\CorpDataLogic;
use MoChat\App\Index\Logic\IndexLogic;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业首页数据统计.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var IndexLogic
     */
    private $indexLogic;

    /**
     * @Inject
     * @var CorpDataLogic
     */
    private $corpDataLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/corpData/index", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }
        return $this->indexLogic->handle();
        //return $this->corpDataLogic->handle();
    }
}
