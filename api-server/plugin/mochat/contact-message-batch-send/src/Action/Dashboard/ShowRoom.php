<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactMessageBatchSend\Logic\ShowLogic;

/**
 * 客户消息群发 - 查看详情.
 * @Controller
 */
class ShowRoom extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShowLogic
     */
    private $showLogic;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    private $workRoomContract;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    private $workContactRoomContract;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $workEmployeeContract;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactMessageBatchSend/showRoom", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        $workRoomId = (int) $this->request->input('workRoomId');
        $room = $this->workRoomContract->getWorkRoomById($workRoomId, ['name', 'owner_id']);
        $owner = $this->workEmployeeContract->getWorkEmployeeById($room['ownerId'], ['name', 'avatar']);
        $room['ownerName'] = $owner['name'];
        $room['ownerAvatar'] = empty($owner['avatar']) ? '' : file_full_url($owner['avatar']);
        $room['total'] = $this->workContactRoomContract->countWorkContactRoomByRoomId($workRoomId);
        $room['totalContact'] = $this->workContactRoomContract->countWorkContactRoomByRoomId($workRoomId, 0, 0, 1);
        $room['todayInsert'] = $this->workContactRoomContract->countAddWorkContactRoomsByRoomIdTime([$workRoomId], date('Y-m-d'), date('Y-m-d', strtotime('+1 day')));
        $room['todayLoss'] = $this->workContactRoomContract->countQuitWorkContactRoomsByRoomIdTime([$workRoomId], date('Y-m-d'), date('Y-m-d', strtotime('+1 day')));
        return $room;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'workRoomId' => 'required|numeric',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomId.required' => '客户群id 必传',
        ];
    }
}
