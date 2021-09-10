<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

/**
 * 群日历-增加.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var RoomCalendarPushContract
     */
    protected $roomCalendarPushService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * StoreLogic constructor.
     */
    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $params = $this->handleParam($user, $params);

        ## 创建活动
        return $this->createRoomCalendar($params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ## 基本信息
        return [
            'room_calendar' => [
                'name' => $params['name'],
                'status' => 1,
                'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
                'corp_id' => $user['corpIds'][0],
                'create_user_id' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ],
            'push' => $this->push($params['push']),
        ];
    }

    /**
     * 推送内容.
     * @throws \League\Flysystem\FileExistsException
     */
    private function push(array $push): array
    {
        foreach ($push as $key => $val) {
            foreach ($val['push_content'] as $k => $v) {
                if ($v['type'] === 'image') {
                    $push[$key]['push_content'][$k]['created_at'] = date('Y-m-d H:i:s');
                    $push[$key]['push_content'][$k]['pic'] = File::uploadBase64Image($v['pic'], 'image/roomCalendar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
                }
            }
        }
        return $push;
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function createRoomCalendar(array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->roomCalendarService->createRoomCalendar($params['room_calendar']);
            foreach ($params['push'] as $key => $val) {
                $val['room_calendar_id'] = $id;
                $val['push_content'] = json_encode($val['push_content']);
                $val['created_at'] = date('Y-m-d H:i:s');
                $this->roomCalendarPushService->createRoomCalendarPush($val);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '群日历创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
