<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Logic\Tag;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;

/**
 * 根据客户同步标签.
 */
class SyncTagByContactLogic
{
    /**
     * 标签.
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * 通讯录 - 客户 - 轨迹互动.
     * @Inject
     * @var ContactEmployeeTrackContract
     */
    private $contactEmployeeTrackService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    private $workContactTagPivotService;

    /**
     * 同步客户标签.
     */
    public function handle(int $corpId, int $contactId, int $employeeId, array $syncTags)
    {
        if (empty($syncTags)) {
            return;
        }

        // 获取客户标签信息
        $tagList = $this->workContactTagService->getWorkContactTagsByCorpIdWxTagId([$corpId], array_column($syncTags, 'tag_id'), ['id', 'wx_contact_tag_id', 'name']);
        // 查询当前客户已存在的标签
        $exitTagList = $this->workContactTagPivotService->getWorkContactTagPivotsByOtherId((int) $contactId, (int) $employeeId, ['contact_tag_id']);
        empty($exitTagList) || $tagList = array_filter($tagList, function ($tag) use ($exitTagList) {
            if (in_array($tag['id'], array_column($exitTagList, 'contactTagId'))) {
                return false;
            }
            return true;
        });

        if (empty($tagList)) {
            return;
        }

        $this->createContactTagPivot($contactId, $employeeId, $syncTags, $tagList);
        $this->createContactTagTrack($corpId, $contactId, $employeeId, $tagList);
    }

    /**
     * 创建标签中间表.
     */
    private function createContactTagPivot(int $contactId, int $employeeId, array $syncTags, array $tagList)
    {
        $syncTags = array_column($syncTags, null, 'tag_id');
        $addContactTag = array_map(function ($tag) use ($contactId, $employeeId, $syncTags) {
            return [
                'contact_id' => $contactId,
                'employee_id' => $employeeId,
                'contact_tag_id' => $tag['id'],
                'type' => $syncTags[$tag['wxContactTagId']]['type'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }, $tagList);
        $this->workContactTagPivotService->createWorkContactTagPivots($addContactTag);
    }

    /**
     * 创建客户轨迹.
     */
    private function createContactTagTrack(int $corpId, int $contactId, int $employeeId, array $tagList)
    {
        $newAddTagNames = array_map(function ($tag) {
            return '【' . $tag['name'] . '】';
        }, $tagList);

        // 给客户打标签事件
        $contactEmployeeTrackData = [
            'employee_id' => $employeeId,
            'contact_id' => $contactId,
            'event' => Event::TAG,
            'content' => sprintf('系统对该客户打标签%s', rtrim(implode('、', $newAddTagNames), '、')),
            'corp_id' => $corpId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        // 记录日志
        $this->contactEmployeeTrackService->createContactEmployeeTrack($contactEmployeeTrackData);
    }
}
