<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\Utils\Url;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;

/**
 * 任务宝 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * 任务宝-裂变.
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * 任务宝-裂变客户参与.
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * 任务宝-裂变欢迎语.
     * @Inject
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * 标签.
     * @Inject
     * @var WorkFissionPushContract
     */
    private $workFissionPushService;

    /**
     * @param int $id id
     * @return array 响应数组
     */
    public function getFission(int $id): array
    {
        $data = [
            'tags'    => [],
            'content' => [],
        ];

        $contact = $this->workFissionContactService->getWorkFissionContactById((int) $id, ['fission_id', 'invite_count']);
        if (empty($contact)) {
            return $data;
        }
        $fission = $this->workFissionService->getWorkFissionById((int) $contact['fissionId'], ['id', 'contact_tags', 'tasks', 'end_time']);
        if (empty($fission)) {
            return $data;
        }
        ## 客户标签
        if (! empty($fission['tags'])) {
            $tagIds                          = array_filter(json_decode($fission['contactTags'], true));
            $tagList                         = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'wx_contact_tag_id']);
            empty($tagList) || $data['tags'] = array_column($tagList, 'wxContactTagId');
        }
        ## 欢迎语
        $welcome = $this->workFissionWelcomeService->getWorkFissionWelcomeByFissionId((int) $contact['fissionId']);
        ## 欢迎语-文本
        empty($welcome['msgText']) || $data['content']['text'] = $welcome['msgText'];

        $data['content']['medium']['mediumType']                   = MediumType::PICTURE_TEXT;
        $data['content']['medium']['mediumContent']['title']       = $welcome['linkTitle'];
        $data['content']['medium']['mediumContent']['description'] = $welcome['linkDesc'];
        $data['content']['medium']['mediumContent']['imagePath']   = $welcome['linkCoverUrl'];
        $data['content']['medium']['mediumContent']['imageLink']   = Url::getAuthRedirectUrl(7, (int) $contact['fissionId']);
        return $data;
    }
}
