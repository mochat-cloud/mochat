<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomAutoPull\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\WorkContact\Constants\Room\Status;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Event\ContactWelcomeEvent;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\RoomAutoPull\Contract\WorkRoomAutoPullContract;

/**
 * 发送欢迎语监听.
 *
 * @Listener(priority=10)
 */
class SendWelcomeListener implements ListenerInterface
{
    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomAutoPullContract
     */
    protected $workRoomAutoPullService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject()
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function listen(): array
    {
        return [
            ContactWelcomeEvent::class,
        ];
    }

    /**
     * @param ContactWelcomeEvent $event
     */
    public function process(object $event)
    {
        $contact = $event->message;

        // 判断是否需要发送欢迎语
        if (! $this->isNeedSendWelcome($contact)) {
            return;
        }

        // 获取欢迎语
        $welcomeContent = $this->getWelcome($contact);
        if (empty($welcomeContent)) {
            $this->logger->debug(sprintf('[自动拉群]客户欢迎语未发送，获取欢迎语为空，客户id: %s', (string) $contact['id']));
            return;
        }

        $this->logger->debug(sprintf('[自动拉群]客户欢迎语匹配成功，即将发送，客户id: %s', (string) $contact['id']));

        // 发送欢迎语
        $this->workContactService->sendWelcome((int) $contact['corpId'], $contact, $contact['welcomeCode'], $welcomeContent);
    }

    /**
     * 判断是否需要发送欢迎语.
     *
     * @return bool
     */
    private function isNeedSendWelcome(array $contact)
    {
        if (! isset($contact['state']) || empty($contact['state'])) {
            return false;
        }

        if (! isset($contact['welcomeCode']) || empty($contact['welcomeCode'])) {
            return false;
        }

        $stateArr = explode('-', $contact['state']);
        if ($stateArr[0] !== $this->getStateName()) {
            return false;
        }

        return true;
    }

    /**
     * 获取来源名称.
     */
    private function getStateName(): string
    {
        return 'workRoomAutoPullId';
    }

    /**
     * 获取欢迎语.
     *
     * @param array $contact 客户
     *
     * @return array[]
     */
    private function getWelcome(array $contact): array
    {
        $stateArr = explode('-', $contact['state']);
        $workRoomAutoPullId = (int) $stateArr[1];

        $data = [];
        $workRoomAutoPull = $this->workRoomAutoPullService->getWorkRoomAutoPullById($workRoomAutoPullId, ['leading_words', 'tags', 'rooms']);
        if (empty($workRoomAutoPull)) {
            return $data;
        }
        // 欢迎语-文本
        empty($workRoomAutoPull['leadingWords']) || $data['text'] = $workRoomAutoPull['leadingWords'];

        // 欢迎语-群二维码
        if (! empty($workRoomAutoPull['rooms'])) {
            $rooms = json_decode($workRoomAutoPull['rooms'], true);
            $roomIds = array_column($rooms, 'roomId');
            $roomList = $this->workRoomService->getWorkRoomsById($roomIds, ['id', 'room_max']);
            empty($roomList) || $roomList = array_column($roomList, null, 'id');
            $countRoomList = $this->workContactRoomService->countWorkContactRoomsByRoomIds($roomIds, (int) Status::NORMAL);
            empty($countRoomList) || $countRoomList = array_column($countRoomList, 'total', 'roomId');
            foreach ($rooms as $room) {
                if (! isset($roomList[$room['roomId']])) {
                    continue;
                }
                $memberNum = isset($countRoomList[$room['roomId']]) ? $countRoomList[$room['roomId']] : 0;
                if ($memberNum >= $room['maxNum'] || $memberNum >= $roomList[$room['roomId']]['roomMax']) {
                    continue;
                }
                $data['medium']['mediumType'] = MediumType::PICTURE;
                $data['medium']['mediumContent']['imagePath'] = $room['roomQrcodeUrl'];
                break;
            }
        }

        return $data;
    }
}
