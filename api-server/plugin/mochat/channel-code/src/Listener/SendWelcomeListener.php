<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ChannelCode\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\ContactWelcomeEvent;
use MoChat\Plugin\ChannelCode\Constants\Status as ChannelCodeStatus;
use MoChat\Plugin\ChannelCode\Constants\WelcomeType;
use MoChat\Plugin\ChannelCode\Contract\ChannelCodeContract;

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
     * 渠道码表.
     * @Inject
     * @var ChannelCodeContract
     */
    private $channelCodeService;

    /**
     * @Inject
     * @var MediumContract
     */
    private $mediumService;

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
            $this->logger->debug(sprintf('[渠道活码]客户欢迎语未发送，获取欢迎语为空，客户id: %s', (string) $contact['id']));
            return;
        }

        $this->logger->debug(sprintf('[渠道活码]客户欢迎语匹配成功，即将发送，客户id: %s', (string) $contact['id']));

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
     *
     * @return string
     */
    private function getStateName()
    {
        return 'channelCode';
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
        $channelCodeId = (int) $stateArr[1];

        $data = [];
        $channelCode = $this->channelCodeService->getChannelCodeById($channelCodeId, ['id', 'welcome_message']);
        if (empty($channelCode)) {
            return $data;
        }

        // 欢迎语为空也不发送
        if (empty($channelCode['welcomeMessage'])) {
            return $data;
        }

        $welcomeMessage = json_decode($channelCode['welcomeMessage'], true);
        if ($welcomeMessage['scanCodePush'] == ChannelCodeStatus::CLOSE || empty($welcomeMessage['messageDetail'])) {
            return $data;
        }

        $data = $this->handleMessageDetail($welcomeMessage['messageDetail']);

        if (isset($data['mediumId']) && ! empty($data['mediumId'])) {
            $medium = $this->getMedium((int) $data['mediumId']);
            if (! empty($medium)) {
                $data['medium'] = $medium;
            }
            unset($data['mediumId']);
        }

        return $data;
    }

    /**
     * @param array $messageDetail 渠道欢迎语
     * @return array 响应数组
     */
    private function handleMessageDetail(array $messageDetail): array
    {
        $data = [];
        $messageDetail = array_column($messageDetail, null, 'type');
        // 特殊欢迎语
        if (isset($messageDetail[WelcomeType::SPECIAL_PERIOD])
            && ! empty($messageDetail[WelcomeType::SPECIAL_PERIOD]['detail'])
            && $messageDetail[WelcomeType::SPECIAL_PERIOD]['status'] == ChannelCodeStatus::OPEN
        ) {
            $detail = $messageDetail[WelcomeType::SPECIAL_PERIOD]['detail'];
            $currentDay = date('Y-m-d');
            foreach ($detail as $val) {
                if (empty($val['timeSlot'])) {
                    continue;
                }
                if (strtotime($currentDay) >= strtotime($val['startDate']) && strtotime($currentDay) <= strtotime($val['endDate'])) {
                    // 00:00 - 00:00 欢迎语
                    $cycleCommon = [];
                    foreach ($val['timeSlot'] as $v) {
                        if (time() >= strtotime($v['startTime']) && time() <= strtotime($v['endTime'])) {
                            empty($v['welcomeContent']) || $data['text'] = $v['welcomeContent'];
                            empty($v['mediumId']) || $data['mediumId'] = $v['mediumId'];
                            return $data;
                        }
                        if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                            $cycleCommon = $v;
                        }
                    }
                    if (! empty($cycleCommon)) {
                        empty($cycleCommon['welcomeContent']) || $data['text'] = $cycleCommon['welcomeContent'];
                        empty($cycleCommon['mediumId']) || $data['mediumId'] = $cycleCommon['mediumId'];
                        return $data;
                    }
                }
            }
        }

        // 周期欢迎语
        if (isset($messageDetail[WelcomeType::PERIODIC])
            && ! empty($messageDetail[WelcomeType::PERIODIC]['detail'])
            && $messageDetail[WelcomeType::PERIODIC]['status'] == ChannelCodeStatus::OPEN
        ) {
            $detail = $messageDetail[WelcomeType::PERIODIC]['detail'];
            $currentWeek = date('w', time());
            foreach ($detail as $val) {
                if (empty($val['timeSlot']) || ! in_array($currentWeek, $val['chooseCycle'])) {
                    continue;
                }
                // 00:00 - 00:00 欢迎语
                $specialCommon = [];
                foreach ($val['timeSlot'] as $v) {
                    if (time() >= strtotime($v['startTime']) && time() <= strtotime($v['endTime'])) {
                        empty($v['welcomeContent']) || $data['text'] = $v['welcomeContent'];
                        empty($v['mediumId']) || $data['mediumId'] = $v['mediumId'];
                        return $data;
                    }
                    if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                        $specialCommon = $v;
                    }
                }
                if (! empty($specialCommon)) {
                    empty($specialCommon['welcomeContent']) || $data['text'] = $specialCommon['welcomeContent'];
                    empty($specialCommon['mediumId']) || $data['mediumId'] = $specialCommon['mediumId'];
                    return $data;
                }
            }
        }
        // 通用欢迎语
        if (isset($messageDetail[WelcomeType::GENERAL])) {
            $detail = $messageDetail[WelcomeType::GENERAL];
            empty($detail['welcomeContent']) || $data['text'] = $detail['welcomeContent'];
            empty($detail['mediumId']) || $data['mediumId'] = $detail['mediumId'];
            return $data;
        }
        return $data;
    }

    /**
     * @param int $mediumId 素材库ID
     * @return array 响应数组
     */
    private function getMedium(int $mediumId): array
    {
        $medium = $this->mediumService->getMediumById($mediumId, ['id', 'type', 'content']);
        return empty($medium) ? [] : [
            'mediumType' => $medium['type'],
            'mediumContent' => json_decode($medium['content'], true),
        ];
    }
}
