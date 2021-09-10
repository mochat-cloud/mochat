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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Utils\File;
use MoChat\App\Utils\Url;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-H5-获取活动海报
 * Class Poster.
 * @Controller
 */
class Poster extends AbstractAction
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
     * @RequestMapping(path="/operation/roomFission/poster", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        ## 群聊
        $room = $this->getRoom($params);
        if (empty($room)) {
            return ['type' => 0, 'room' => [], 'poster' => []];
        }
        ## 客户是否入群
        $unionRoom = $this->workContactRoomService->getWorkContactRoomsByRoomIdUnion((int) $room['id'], $params['union_id']);
        ## 客户未入群-返回群聊
        if (empty($unionRoom)) {
            $room['roomQrcode'] = file_full_url($room['roomQrcode']);
            $this->createRoomFissionContact($params, (int) $room['id']);
            unset($room['id'], $room['roomMax'], $room['contact_num']);
            return ['type' => 1, 'room' => $room, 'poster' => []];
        }
        ## 客户已入群-返回海报
        $poster = $this->roomFissionPosterService->getRoomFissionPosterByFissionId((int) $params['fission_id'], ['fission_id', 'cover_pic', 'avatar_show', 'nickname_show', 'nickname_color', 'qrcode_w', 'qrcode_h', 'qrcode_x', 'qrcode_y']);
        $poster['coverPic'] = file_full_url($poster['coverPic']);
        $poster['qrCodeUrl'] = Url::getAuthRedirectUrl(8, $params['fission_id'], [
            'parent_union_id' => $params['union_id'],
            'wx_user_id' => '',
        ]);

        $this->createRoomFissionContact($params, (int) $room['id']);
        return ['type' => 2, 'room' => [], 'poster' => $poster];
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
            'nickname' => 'required',
            'avatar' => 'required',
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
            'nickname.required' => 'nickname 必传',
            'avatar.required' => 'avatar 必传',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function getRoom($params): array
    {
        ## 群聊
        $rooms = $this->roomFissionRoomService->getRoomFissionRoomByFissionId((int) $params['fission_id'], ['room_qrcode', 'room', 'room_max']);
        $room = [];
        foreach ($rooms as $k => $v) {
            $roomArr = json_decode($v['room'], true, 512, JSON_THROW_ON_ERROR);
            $personNum = $this->workContactRoomService->countWorkContactRoomByRoomId((int) $roomArr['id'], 0, 1);
            if ((int) $v['roomMax'] > $personNum) {
                $room = $roomArr;
                $room['roomQrcode'] = $v['roomQrcode'];
                break;
            }
        }
        return $room;
    }

    /**
     * 生成客户记录.
     */
    private function createRoomFissionContact(array $params, int $roomId)
    {
        $contactRecord = $this->roomFissionContactService->getRoomFissionContactByRoomIdUnionIdFissionID($roomId, $params['union_id'], (int) $params['fission_id'], ['id']);
        if (empty($contactRecord)) {
            $fission = $this->roomFissionService->getRoomFissionById((int) $params['fission_id'], ['corp_id']);
            $contact = $this->workContactService->getWorkContactByCorpIdUnionId($fission['corpId'], $params['union_id'], ['id', 'wx_external_userid']);
            $contactData = [
                'fission_id' => $params['fission_id'],
                'union_id' => $params['union_id'],
                'nickname' => $params['nickname'],
                'avatar' => File::uploadUrlImage($params['avatar'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
                'parent_union_id' => $params['parentUnionId'],
                'level' => empty($params['parentUnionId']) ? 1 : 2,
                'contact_id' => empty($contact) ? 0 : $contact['id'],
                'employee' => $params['wxUserId'],
                'is_new' => 0,
                'external_user_id' => empty($contact) ? '' : $contact['wxExternalUserid'],
                'room_id' => $roomId,
            ];
            ## 数据操作
            Db::beginTransaction();
            try {
                $this->roomFissionContactService->createRoomFissionContact($contactData);
                Db::commit();
            } catch (\Throwable $e) {
                Db::rollBack();
                $this->logger->error(sprintf('%s [%s] %s', '客户记录失败', date('Y-m-d H:i:s'), $e->getMessage()));
                $this->logger->error($e->getTraceAsString());
                throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
            }
        }
    }
}
