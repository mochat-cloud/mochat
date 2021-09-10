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
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

/**
 * 群日历-修改.
 *
 * Class StoreLogic
 */
class UpdateLogic
{
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
     * UpdateLogic constructor.
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
        $data = $this->handleParam($params['push']);

        ## 创建活动
        return $this->createRoomCalendarPush((int) $params['id'], $data);
    }

    /**
     * 处理参数.
     * @param array $push 接受参数
     * @throws \League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    private function handleParam(array $push): array
    {
        ## 基本信息
        foreach ($push as $key => $val) {
            foreach ($val['push_content'] as $k => $v) {
                $push[$key]['push_content'][$k]['created_at'] = date('Y-m-d H:i:s');
                if ($v['type'] == 'image') {
                    $push[$key]['push_content'][$k]['pic'] = File::uploadBase64Image($v['pic'], 'image/roomCalendar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
                }
            }
        }
        return $push;
    }

    /**
     * 创建群日历推送.
     * @return array|int[]
     */
    private function createRoomCalendarPush(int $id, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            foreach ($params as $key => $val) {
                $val['room_calendar_id'] = $id;
                $val['push_content'] = json_encode($val['push_content'], JSON_THROW_ON_ERROR);
                if (isset($val['id']) && $val['id'] > 0) {
                    $id = $val['id'];
                    unset($val['id']);
                    $this->roomCalendarPushService->updateRoomCalendarPushById((int) $id, $val);
                } else {
                    $this->roomCalendarPushService->createRoomCalendarPush($val);
                }
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '群日历推送修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [];
    }
}
