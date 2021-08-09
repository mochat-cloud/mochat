<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomAutoPull\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\WorkContact\Constants\Room\Status;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\RoomAutoPull\Contract\WorkRoomAutoPullContract;

/**
 * 自动拉群 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
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
     * 标签.
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * @param int $workRoomAutoPullId 自动拉群ID
     * @return array 响应数组
     */
    public function getWorkRoomAutoPull(int $workRoomAutoPullId): array
    {
        $data = [
            'tags'    => [],
            'content' => [],
        ];
        $workRoomAutoPull = $this->workRoomAutoPullService->getWorkRoomAutoPullById($workRoomAutoPullId, ['leading_words', 'tags', 'rooms']);
        if (empty($workRoomAutoPull)) {
            return $data;
        }
        ## 欢迎语-文本
        empty($workRoomAutoPull['leadingWords']) || $data['content']['text'] = $workRoomAutoPull['leadingWords'];
        ## 客户标签
        if (! empty($workRoomAutoPull['tags'])) {
            $tagIds                          = array_filter(json_decode($workRoomAutoPull['tags'], true));
            $tagList                         = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'wx_contact_tag_id']);
            empty($tagList) || $data['tags'] = array_column($tagList, 'wxContactTagId');
        }
        ## 欢迎语-群二维码
        if (! empty($workRoomAutoPull['rooms'])) {
            $rooms                                  = json_decode($workRoomAutoPull['rooms'], true);
            $roomIds                                = array_column($rooms, 'roomId');
            $roomList                               = $this->workRoomService->getWorkRoomsById($roomIds, ['id', 'room_max']);
            empty($roomList) || $roomList           = array_column($roomList, null, 'id');
            $countRoomList                          = $this->workContactRoomService->countWorkContactRoomsByRoomIds($roomIds, (int) Status::NORMAL);
            empty($countRoomList) || $countRoomList = array_column($countRoomList, 'total', 'roomId');
            foreach ($rooms as $room) {
                if (! isset($roomList[$room['roomId']])) {
                    continue;
                }
                $memberNum = isset($countRoomList[$room['roomId']]) ? $countRoomList[$room['roomId']] : 0;
                if ($memberNum >= $room['maxNum'] || $memberNum >= $roomList[$room['roomId']]['roomMax']) {
                    continue;
                }
                $data['content']['medium']['mediumType']                 = MediumType::PICTURE;
                $data['content']['medium']['mediumContent']['imagePath'] = $room['roomQrcodeUrl'];
                break;
            }
        }
        return $data;
    }
}
