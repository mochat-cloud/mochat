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

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\QueueService\Tag\SyncContactTagApply;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 同步企业客户标签.
 *
 * Class SynContactTag
 * @Controller
 */
class SynContactTag extends AbstractAction
{
    /**
     * @var SyncContactTagApply
     */
    private $service;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workContactTag/synContactTag", methods="PUT")
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //同步企业客户标签
        $this->service = make(SyncContactTagApply::class);

        $this->service->handle(user()['corpIds'][0]);
    }
}
