<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-H5-查看助力进度
 * Class InviteFriends.
 * @Controller
 */
class InviteFriends extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * @Inject
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * @Inject
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

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
     * @RequestMapping(path="/operation/roomFission/inviteFriends", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        ## 群裂变信息
        $fission = $this->roomFissionService->getRoomFissionById((int) $params['fission_id'], ['target_count', 'new_friend', 'delete_invalid']);
        ## 客户信息
        $contactRecord = $this->roomFissionContactService->getRoomFissionContactByUnionIdFissionID($params['union_id'], (int) $params['fission_id'], ['id', 'nickname', 'avatar', 'invite_count']);
        $isNew = $fission['newFriend'] === 1 ? 1 : 2;
        $loss = $fission['deleteInvalid'] === 1 ? 0 : 2;
        $inviteFriends = $this->roomFissionContactService->getRoomFissionContactByParentUnionId($params['union_id'], 1, $isNew, $loss, ['nickname', 'avatar', 'created_at']);
        foreach ($inviteFriends as $k => $v) {
            $inviteFriends[$k]['avatar'] = file_full_url($v['avatar']);
        }
        return ['nickname' => $contactRecord['nickname'], 'avatar' => file_full_url($contactRecord['avatar']), 'success_num' => $contactRecord['inviteCount'], 'diff_num' => $fission['targetCount'] - $contactRecord['inviteCount'], 'invite_friends' => $inviteFriends];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'fission_id' => 'required',
            'union_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'fission_id.required' => 'fission_id 必传',
            'union_id.required' => 'union_id 必传',
        ];
    }
}
