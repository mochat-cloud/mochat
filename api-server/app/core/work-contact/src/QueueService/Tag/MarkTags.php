<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\QueueService\Tag;

use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

class MarkTags
{
    /**
     * @AsyncQueueMessage(pool="contact")
     *
     * @param int $corpId
     * @param int $contactId
     * @param int $employeeId
     * @param array $tagIds
     */
    public function handle(int $corpId, int $contactId, int $employeeId, array $tagIds)
    {
        try {
            if (empty($tagIds)) {
                return;
            }

            $employeeService = make(WorkEmployeeContract::class);
            $contactService = make(WorkContactContract::class);
            $logger = make(StdoutLoggerInterface::class);

            $contact = $contactService->getWorkContactById($contactId, ['wx_external_userid']);
            if (empty($contact)) {
                $logger->error(sprintf('打标签失败，找不到客户信息，客户id: %s', (string) $contactId));
            }

            $employee = $employeeService->getWorkEmployeeById($employeeId, ['wx_user_id']);
            if (empty($employee)) {
                $logger->error(sprintf('打标签失败，找不到员工信息，员工id: %s', (string) $employeeId));
            }

            $tagIds = $this->filterTagIds($contactId, $employeeId, $tagIds);
            if (empty($tagIds)) {
                return;
            }

            $this->createWorkContactTagPivots($contactId, $employeeId, $tagIds);

            $tagInfo = $this->getWorkContactTag($tagIds);
            $this->markTagsToWorkServer($corpId, $contact['wxExternalUserid'], $employee['wxUserId'], $tagInfo['wxTagIds']);
            $this->recordContactTrack($corpId, $contactId, $employeeId, $tagInfo['tagNames']);
            $logger->debug(sprintf('客户打标签执行成功，客户id: %s', (string) $contactId));
        } catch (\Throwable $e) {
            $logger->error(sprintf('客户打标签执行失败，客户id: %s，错误信息: %s', (string) $contactId, $e->getMessage()));
            $logger->error($e->getTraceAsString());
        }
    }

    /**
     * 过滤出本次需要添加的标签id
     *
     * @param int $contactId
     * @param int $employeeId
     * @param array $tagIds
     * @return array
     */
    private function filterTagIds(int $contactId, int $employeeId, array $tagIds)
    {
        $workContactTagPivotService = make(WorkContactTagPivotContract::class);
        $contactTagPivot = $workContactTagPivotService->getWorkContactTagPivotsByOtherId($contactId, $employeeId, ['contact_tag_id']);

        if (empty($contactTagPivot)) {
            return $tagIds;
        }

        $alreadyTagIds = array_column($contactTagPivot, 'contactTagId');
        return array_diff($tagIds, $alreadyTagIds);
    }

    /**
     * 添加客户标签至数据库
     *
     * @param int $contactId
     * @param int $employeeId
     * @param array $tagIds
     */
    private function createWorkContactTagPivots(int $contactId, int $employeeId, array $tagIds)
    {
        $workContactTagPivotService = make(WorkContactTagPivotContract::class);
        $data = [];

        foreach ($tagIds as $val) {
            $data[] = [
                'contact_id' => $contactId,
                'employee_id' => $employeeId,
                'contact_tag_id' => $val,
                'type' => 1,
            ];
        }

        $res = $workContactTagPivotService->createWorkContactTagPivots($data);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
        }
    }

    /**
     * 同步打标至企业微信服务器
     *
     * @param $corpId
     * @param string $contactWxExternalUserId
     * @param string $employeeWxUserId
     * @param array $tagIds
     */
    private function markTagsToWorkServer($corpId, string $contactWxExternalUserId, string $employeeWxUserId, array $tagIds)
    {
        if (empty($tagIds)) {
            return;
        }

        $weWorkContactApp = make(WeWorkFactory::class)->getContactApp($corpId);
        $logger = make(StdoutLoggerInterface::class);

        $tagData = [
            'userid' => $employeeWxUserId,
            'external_userid' => $contactWxExternalUserId,
            'add_tag' => $tagIds,
        ];

        $wxTagRes = $weWorkContactApp->external_contact->markTags($tagData);
        if ($wxTagRes['errcode'] != 0) {
            $logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户打标签错误', date('Y-m-d H:i:s'), json_encode($tagData), json_encode($wxTagRes)));
        }
    }

    /**
     * 根据标签id获取标签微信id和标签名.
     * @param array $tagIds
     *
     * @return array
     */
    private function getWorkContactTag($tagIds): array
    {
        $workContactTagService = make(WorkContactTagContract::class);
        $wxTagIds = [];
        $tagNames = [];

        $tagInfo = $workContactTagService->getWorkContactTagsById($tagIds);
        if (! empty($tagInfo)) {
            $wxTagIds = array_column($tagInfo, 'wxContactTagId');
            $tagNames = array_column($tagInfo, 'name');
        }

        return [
            'wxTagIds' => $wxTagIds,
            'tagNames' => $tagNames,
        ];
    }

    /**
     * 记录客户互动轨迹.
     *
     * @param int $corpId
     * @param int $contactId
     * @param int $employeeId
     * @param array $addTagNames
     */
    private function recordContactTrack(int $corpId, int $contactId, int $employeeId, array $addTagNames)
    {
        if (empty($addTagNames)) {
            return;
        }

        $content = '系统对该客户打标签';
        $tagCount = count($addTagNames);
        foreach ($addTagNames as $key => $value) {
            if ($key != $tagCount - 1) {
                $content .= '【' . $value . '】、';
            } else {
                $content .= '【' . $value . '】';
            }
        }

        $data = [
            'employee_id' => $employeeId,
            'contact_id' => $contactId,
            'content' => $content,
            'corp_id' => $corpId,
            'event' => Event::TAG,
        ];

        $contactTrackService = make(ContactEmployeeTrackContract::class);
        $contactTrackService->createContactEmployeeTrack($data);
    }
}
