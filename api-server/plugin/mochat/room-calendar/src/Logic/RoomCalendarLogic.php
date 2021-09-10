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
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\Url;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

class RoomCalendarLogic
{
    use AppTrait;

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
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    private $workAgentService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array|void
     */
    public function handle()
    {
        try {
            ## 待发送信息
            $day = date('Y-m-d H:i', strtotime('+1 minute'));
            $push = $this->roomCalendarPushService->getRoomCalendarPushByDay($day, ['id', 'room_calendar_id', 'name', 'day', 'push_content']);
//            $this->logger->info('群日历任务推送内容'. json_encode($push, JSON_THROW_ON_ERROR));
            if (empty($push)) {
                return;
            }
            foreach ($push as $key => $val) {
                ##群日历  管理员
                $calendar = $this->roomCalendarService->getRoomCalendarById((int) $val['roomCalendarId'], ['id', 'rooms', 'corp_id', 'create_user_id']);
                $employee = $this->workEmployeeService->getWorkEmployeeById($calendar['createUserId'], ['name']);
                ## 推送
                $roomArr = json_decode($calendar['rooms'], true, 512, JSON_THROW_ON_ERROR);
                $room = array_count_values(array_column($roomArr, 'ownerId'));
                $messageRemind = make(MessageRemind::class);
                foreach ($room as $k => $v) {
                    $ownerInfo = $this->workEmployeeService->getWorkEmployeeById($k, ['wx_user_id']);
                    $this->logger->info('群日历任务-群主' . $k);
//                    $link    = Url::getSidebarBaseUrl() . "/***?id={$calendar['id']}&owner={$k}&day={$val['day']}";
                    $link = '#'; // TODO 需要新增sidebar页面
                    $content = "管理员{$employee['name']}创建了群日历推送任务，提醒你给{$v}个群聊发送消息<a href='{$link}'>查看推送详情 </a>";
                    $messageRemind->sendToEmployee(
                        (int) $calendar['corpId'],
                        $ownerInfo['wxUserId'],
                        'text',
                        $content
                    );
                }
                $this->roomCalendarPushService->updateRoomCalendarPushById($val['id'], ['status' => 2]);
            }
            $this->logger->info('群日历任务推送完成' . date('Y-m-d H:i:s', time()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '群日历推送消息失败', date('Y-m-d H:i:s'), $e->getMessage()));
        }
    }
}
