<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContact;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Middleware\PermissionMiddleware;
use App\QueueService\WorkContact\SynContactApply;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 同步客户.
 *
 * Class SynContact
 * @Controller
 */
class SynContact extends AbstractAction
{
    /**
     * @Inject
     * @var SynContactApply
     */
    private $service;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $workEmployee;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    private $corp;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workContact/synContact", methods="PUT")
     */
    public function handle()
    {
        $corpIds = user()['corpIds'];
        if (count($corpIds) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //查询企业微信id
        $corpInfo = $this->corp->getCorpById($corpIds[0], ['wx_corpid']);
        if (empty($corpInfo)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '企业授权信息错误');
        }

        //获取成员信息
        $employee = $this->workEmployee->getWorkEmployeesByCorpIdWxUserIdNotNull([$corpIds[0]], ['id', 'wx_user_id']);
        if (empty($employee)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到有效的企业微信成员信息');
        }
        ## 异步队列处理
        foreach ($employee as $v) {
            $this->service->handle($v, (int) $corpIds[0], $corpInfo['wxCorpid']);
        }
    }
}
