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

        //当前企业id
        $corpId = $corpIds[0];

        //查询企业微信id
        $corpInfo = $this->corp->getCorpById($corpId, ['wx_corpid']);
        if (empty($corpInfo)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '查询不到企业微信id');
        }

        //获取成员信息
        $employee = $this->workEmployee->getWorkEmployeesByCorpIdWxUserIdNotNull([$corpId], ['id', 'wx_user_id']);
        if (empty($employee)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到有效的企业微信成员信息');
        }

        $this->service = make(SynContactApply::class);
        $this->service->handle($employee, $corpId);
    }
}
