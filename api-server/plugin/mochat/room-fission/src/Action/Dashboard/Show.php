<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionInviteContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionWelcomeContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-数据总览
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 海报.
     * @Inject
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * 欢迎语.
     * @Inject
     * @var RoomFissionWelcomeContract
     */
    protected $roomFissionWelcomeService;

    /**
     * 群聊.
     * @Inject
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * 邀请用户.
     * @Inject
     * @var RoomFissionInviteContract
     */
    protected $roomFissionInviteService;

    /**
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomFission/show", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $this->validated($this->request->all());
        $id = (int) $this->request->input('id');
        ## 总进群人数
        $statistics['join_room_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 1, 2, 2, 0, '');
        ## 总完成人数
        $statistics['finish_person_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 2, 1, 2, 0, '');
        ## 总流失人数
        $statistics['loss_person_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 2, 2, 1, 0, '');
        ## 总净增人数
        $statistics['insert_person_num'] = $statistics['join_room_num'] - $statistics['loss_person_num'];

        $day = date('Y-m-d');
        ## 今日总进群人数
        $statistics['today_join_room_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 1, 2, 2, 0, $day);
        ## 今日总完成人数
        $statistics['today_finish_person_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 2, 1, 2, 0, $day);
        ## 今日总流失人数
        $statistics['today_loss_person_num'] = $this->roomFissionContactService->countRoomFissionContactByFissionID($id, 2, 2, 1, 0, $day);
        ## 今日总净增人数
        $statistics['today_insert_person_num'] = $statistics['today_join_room_num'] - $statistics['today_loss_person_num'];

        ## 群聊
        $rooms = $this->roomFissionRoomService->getRoomFissionRoomByFissionId($id, ['room']);
        $roomOwner = [];
        foreach ($rooms as $k => $v) {
            $room = json_decode($v['room'], true, 512, JSON_THROW_ON_ERROR);
            $roomOwner[] = $this->workEmployeeService->getWorkEmployeeById((int) $room['ownerId'], ['id', 'name']);
        }

        return ['statistics' => $statistics, 'room_owner' => array_unique($roomOwner, SORT_REGULAR)];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => 'id 必传',
        ];
    }
}
