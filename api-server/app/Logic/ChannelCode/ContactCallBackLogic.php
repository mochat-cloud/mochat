<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode;

use App\Constants\ChannelCode\Status as ChannelCodeStatus;
use App\Constants\ChannelCode\WelcomeType;
use App\Contract\ChannelCodeServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 渠道码 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * 渠道码表.
     * @Inject
     * @var ChannelCodeServiceInterface
     */
    private $channelCodeService;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $workContactTagService;

    /**
     * @Inject
     * @var MediumServiceInterface
     */
    private $mediumService;

    /**
     * @param int $channelCodeId 渠道码ID
     * @return array 响应数组
     */
    public function getChannelCode(int $channelCodeId): array
    {
        $data = [
            'tags'    => [],
            'content' => [],
        ];
        $channelCode = $this->channelCodeService->getChannelCodeById($channelCodeId, ['id', 'tags', 'welcome_message']);
        if (empty($channelCode)) {
            return $data;
        }
        ## 客户标签
        if (! empty($channelCode['tags'])) {
            $tagIds                          = array_filter(json_decode($channelCode['tags'], true));
            $tagList                         = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'wx_contact_tag_id']);
            empty($tagList) || $data['tags'] = array_column($tagList, 'wxContactTagId');
        }
        ## 欢迎语
        if (! empty($channelCode['welcomeMessage'])) {
            $welcomeMessage = json_decode($channelCode['welcomeMessage'], true);
            if ($welcomeMessage['scanCodePush'] == ChannelCodeStatus::OPEN && ! empty($welcomeMessage['messageDetail'])) {
                $data['content'] = $this->handleMessageDetail($welcomeMessage['messageDetail']);
            }
        }
        if (isset($data['content']['mediumId'])) {
            $data['content']['medium'] = $this->getMedium((int) $data['content']['mediumId']);
            unset($data['content']['mediumId']);
        }
        return $data;
    }

    /**
     * @param array $messageDetail 渠道欢迎语
     * @return array 响应数组
     */
    private function handleMessageDetail(array $messageDetail): array
    {
        $data          = [];
        $messageDetail = array_column($messageDetail, null, 'type');
        ## 特殊欢迎语
        if (isset($messageDetail[WelcomeType::SPECIAL_PERIOD])
            && ! empty($messageDetail[WelcomeType::SPECIAL_PERIOD]['detail'])
            && $messageDetail[WelcomeType::SPECIAL_PERIOD]['status'] == ChannelCodeStatus::OPEN
        ) {
            $detail     = $messageDetail[WelcomeType::SPECIAL_PERIOD]['detail'];
            $currentDay = date('Y-m-d');
            foreach ($detail as $val) {
                if (empty($val['timeSlot'])) {
                    continue;
                }
                if (strtotime($currentDay) >= strtotime($val['startDate']) && strtotime($currentDay) <= strtotime($val['endDate'])) {
                    ## 00:00 - 00:00 欢迎语
                    $cycleCommon = [];
                    foreach ($val['timeSlot'] as $v) {
                        if (time() >= strtotime($v['startTime']) && time() <= strtotime($v['endTime'])) {
                            empty($v['welcomeContent']) || $data['text'] = $v['welcomeContent'];
                            empty($v['mediumId']) || $data['mediumId']   = $v['mediumId'];
                            return $data;
                        }
                        if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                            $cycleCommon = $v;
                        }
                    }
                    if (! empty($cycleCommon)) {
                        empty($cycleCommon['welcomeContent']) || $data['text'] = $cycleCommon['welcomeContent'];
                        empty($cycleCommon['mediumId']) || $data['mediumId']   = $cycleCommon['mediumId'];
                        return $data;
                    }
                }
            }
        }
        ## 周期欢迎语
        if (isset($messageDetail[WelcomeType::PERIODIC])
            && ! empty($messageDetail[WelcomeType::PERIODIC]['detail'])
            && $messageDetail[WelcomeType::PERIODIC]['status'] == ChannelCodeStatus::OPEN
        ) {
            $detail      = $messageDetail[WelcomeType::PERIODIC]['detail'];
            $currentWeek = date('w', time());
            foreach ($detail as $val) {
                if (empty($val['timeSlot']) || ! in_array($currentWeek, $val['chooseCycle'])) {
                    continue;
                }
                ## 00:00 - 00:00 欢迎语
                $specialCommon = [];
                foreach ($val['timeSlot'] as $v) {
                    if (time() >= strtotime($v['startTime']) && time() <= strtotime($v['endTime'])) {
                        empty($v['welcomeContent']) || $data['text'] = $v['welcomeContent'];
                        empty($v['mediumId']) || $data['mediumId']   = $v['mediumId'];
                        return $data;
                    }
                    if ($v['startTime'] == '00:00' && $v['endTime'] == '00:00') {
                        $specialCommon = $v;
                    }
                }
                if (! empty($specialCommon)) {
                    empty($specialCommon['welcomeContent']) || $data['text'] = $specialCommon['welcomeContent'];
                    empty($specialCommon['mediumId']) || $data['mediumId']   = $specialCommon['mediumId'];
                    return $data;
                }
            }
        }
        ## 通用欢迎语
        if (isset($messageDetail[WelcomeType::GENERAL])) {
            $detail                                           = $messageDetail[WelcomeType::GENERAL];
            empty($detail['welcomeContent']) || $data['text'] = $detail['welcomeContent'];
            empty($detail['mediumId']) || $data['mediumId']   = $detail['mediumId'];
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
            'mediumType'    => $medium['type'],
            'mediumContent' => json_decode($medium['content'], true),
        ];
    }
}
