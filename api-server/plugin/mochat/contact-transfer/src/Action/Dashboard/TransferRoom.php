<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Plugin\ContactTransfer\Contract\WorkTransferLogContract;
use MoChat\Plugin\ContactTransfer\Logic\TransferRoomLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class TransferRoom extends AbstractAction
{
    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkTransferLogContract
     */
    protected $workTransferLogService;

    /**
     * @var TransferRoomLogic
     */
    protected $transferRoomLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(TransferRoomLogic $transferRoomLogic, RequestInterface $request)
    {
        $this->transferRoomLogic = $transferRoomLogic;
        $this->request = $request;
    }

    /**
     * 分配离职客服群接口.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactTransfer/room", methods="POST")
     */
    public function handle(): array
    {
        $params['list'] = $this->request->input('list');                            //群WxId数组 json ["wrWGBlCwAAeddGe9QCOLVI2CRsdmihLQ", "wrWGBlCwAAOhDRr6r1eW7O6IKOHSO3hg"]
        if (! $params['list']) {
            $params['list'] = '[]';
        }
        $params['list'] = json_decode($params['list']);
        $params['takeoverUserId'] = $this->request->input('takeoverUserId');        //接替成员的WxId

        $user = user();
        $params['corpId'] = $user['corpIds'][0];

        $res = $this->transferRoomLogic->transferRoom($params);

        foreach ($params['list'] as $param) {
            $isExist = false;

            foreach ($res as $re) {
                if ($re['chat_id'] == $param) {
                    $isExist = true;
                    break;
                }
            }

            //如果不在错误列表中
            if (! $isExist) {
                $this->workTransferLogService->createWorkTransferLog([
                    'corp_id' => $params['corpId'],
                    'status' => 1,
                    'type' => 2,
                    'name' => $this->workRoomService->getWorkRoomByCorpIdWxChatId($params['corpId'], $param)['name'],
                    'contact_id' => $param,
                    'takeover_employee_id' => $params['takeoverUserId'],
                ]);
            }
        }

        return $res;
    }
}
