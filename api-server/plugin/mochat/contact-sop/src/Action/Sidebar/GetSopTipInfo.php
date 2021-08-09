<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Action\Sidebar;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactSop\Logic\GetSopTipInfoLogic;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;

/**
 * h5侧边栏提示接口.
 * @Controller
 */
class GetSopTipInfo extends AbstractAction
{
    /**
     * @Inject
     * @var GetSopTipInfoLogic
     */
    protected $getSopTipInfoLogic;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(GetSopTipInfoLogic $getSopTipInfoLogic, RequestInterface $request)
    {
        $this->getSopTipInfoLogic = $getSopTipInfoLogic;
        $this->request            = $request;
    }

    /**
     * 规则详情.
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/contactSop/getSopTipInfo", methods="GET")
     */
    public function handle(): array
    {
        $params['corpId'] = (int) $this->request->input('corpId');          //企业id
        #修改
        $employeeWxId = (int) $this->request->input('employeeWxId');   //客服WxId
        $contactWxId  = (int) $this->request->input('contactWxId');     //客户WxId
        $employee     = $this->workEmployeeService->getWorkEmployeeById($employeeWxId, ['wx_user_id']);
        if (empty($employee)) {
            return [];
        }
        $params['employeeWxId'] = $employee['wxUserId'];
        $contact                = $this->workContactService->getWorkContactById($contactWxId, ['wx_external_userid']);
        if (empty($contact)) {
            return [];
        }
        $params['contactWxId'] = $contact['wxExternalUserid'];

        return $this->getSopTipInfoLogic->handle($params);
    }
}
