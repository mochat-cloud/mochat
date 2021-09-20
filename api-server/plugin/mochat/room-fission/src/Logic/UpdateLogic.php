<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionInviteContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionWelcomeContract;

/**
 * 群裂变-更新.
 *
 * Class StoreLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * 海报.
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * 欢迎语.
     * @var RoomFissionWelcomeContract
     */
    protected $roomFissionWelcomeService;

    /**
     * 群聊.
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * 邀请用户.
     * @var RoomFissionInviteContract
     */
    protected $roomFissionInviteService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $data = $this->handleParam($user, $params);
        ## 更新活动
        $this->updateRoomFission((int) $params['fission']['id'], $data);

        return [];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $data['fission'] = [
            'end_time' => $params['fission']['end_time'],
            'receive_employees' => json_encode($params['fission']['receive_employees'], JSON_THROW_ON_ERROR),
            'auto_pass' => (int) $params['fission']['auto_pass'],
        ];

        ##海报
        $data['poster'] = [
            'cover_pic' => File::uploadBase64Image($params['poster']['cover_pic'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
            'avatar_show' => (int) $params['poster']['avatar_show'],
            'nickname_show' => (int) $params['poster']['nickname_show'],
            'nickname_color' => $params['poster']['nickname_color'],
            'qrcode_w' => $params['poster']['qrcode_w'],
            'qrcode_h' => $params['poster']['qrcode_h'],
            'qrcode_x' => $params['poster']['qrcode_x'],
            'qrcode_y' => $params['poster']['qrcode_y'],
        ];
        ##群聊
        $data['rooms'] = $params['rooms'];
        return $data;
    }

    /**
     * 创建活动.
     * @param int $id 活动id
     * @param array $params 参数
     */
    private function updateRoomFission(int $id, array $params): void
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $this->roomFissionService->updateRoomFissionById($id, $params['fission']);
            $poster = $this->roomFissionPosterService->getRoomFissionPosterByFissionId($id);
            $this->roomFissionPosterService->updateRoomFissionPosterById($poster['id'], $params['poster']);
            foreach ($params['rooms'] as $k => $v) {
                $room_id = $v['room']['id'];
                $v['room'] = json_encode($v['room'], JSON_THROW_ON_ERROR);
                $v['room_qrcode'] = File::uploadBase64Image($v['room_qrcode'], 'image/roomFission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
                $this->roomFissionRoomService->updateRoomFissionRoomByFissionIdRoomId($id, $room_id, $v);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '活动更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '活动更新失败'); //$e->getMessage()
        }
    }
}
