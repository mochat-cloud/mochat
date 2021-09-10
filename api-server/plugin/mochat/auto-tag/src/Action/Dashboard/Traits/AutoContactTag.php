<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Action\Dashboard\Traits;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkContact\QueueService\UpdateContactTagApply;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

trait AutoContactTag
{
    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @var UpdateContactTagApply
     */
    protected $contactTagService;

    /**
     * 互动轨迹表.
     * @Inject
     * @var ContactEmployeeTrackContract
     */
    private $track;

    /**
     * 参数.
     * @var array
     */
    private $params;

    /**
     * 客户打标签 $params = ['contactId'=>59,'employeeId'=>5,'tagArr'=>[1,2,5],'employeeWxUserId'=>,'contactWxExternalUserid'=>].
     * @param mixed $params
     */
    protected function autoTag($params): void
    {
        $this->params = $params;
        $tagIds = $this->params['tagArr'];
        $addWxTag = [];
        ## 查询员工对客户所打标签
        $contactTagPivot = $this->workContactTagPivotService->getWorkContactTagPivotsByOtherId($this->params['contactId'], $this->params['employeeId'], ['contact_tag_id']);
        ## 若客户已有标签
        if (! empty($contactTagPivot)) {
            ## 已有标签id
            $alreadyTagIds = array_column($contactTagPivot, 'contactTagId');
            ## 本次修改标签id
            $currentTagIds = $this->params['tagArr'];
            ## 本次修改的标签id与已有标签id取差集 则为本次需要添加的标签
            $tagIds = array_diff($currentTagIds, $alreadyTagIds);
        }

        ## 标签不为空
        if (! empty($tagIds)) {
            $data = [];
            foreach ($tagIds as $val) {
                $data[] = [
                    'contact_id' => $this->params['contactId'],
                    'employee_id' => $this->params['employeeId'],
                    'contact_tag_id' => $val,
                ];
            }

            ## 查询标签信息
            $tagInfo = $this->getWorkContactTag($tagIds);
            ## 需要同步添加的微信标签
            $addWxTag = $tagInfo['wxTagIds'];
            ## 标签名称
            $addTagName = $tagInfo['tagName'];

            ## 添加本地客户标签
            $res = $this->workContactTagPivotService->createWorkContactTagPivots($data);
            if ($res != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
            }
            ## 同步到企业微信
            $this->contactTagService = make(UpdateContactTagApply::class);
            $wxParams = [
                'add_tag' => $addWxTag,
                'userid' => $this->params['employeeWxUserId'],
                'external_userid' => $this->params['contactWxExternalUserid'],
            ];
            $corpId = isset($params['corpId']) ? $params['corpId'] : user()['corpIds'][0];
            $this->contactTagService->handle($wxParams, $corpId);
            ## 记录标签轨迹
            $content = '系统对该客户打标签';
            foreach ($addTagName as $key => $value) {
                if ($key != count($addTagName) - 1) {
                    $content .= '【' . $value . '】、';
                } else {
                    $content .= '【' . $value . '】';
                }
            }
            $this->recordTrack($content, Event::TAG, $corpId);
        }
    }

    /**
     * 根据标签id获取标签微信id和标签名.
     * @param $tagIds
     */
    private function getWorkContactTag($tagIds): array
    {
        $wxTagIds = [];
        $tagName = [];

        $tagInfo = $this->workContactTagService->getWorkContactTagsById($tagIds);
        if (! empty($tagInfo)) {
            $wxTagIds = array_column($tagInfo, 'wxContactTagId');
            $tagName = array_column($tagInfo, 'name');
        }

        return [
            'wxTagIds' => $wxTagIds,
            'tagName' => $tagName,
        ];
    }

    /**
     * 记录轨迹.
     * @param $content
     * @param $event
     * @param $corpId
     */
    private function recordTrack($content, $event, $corpId): void
    {
        $data = [
            'employee_id' => $this->params['employeeId'],
            'contact_id' => $this->params['contactId'],
            'content' => $content,
            'corp_id' => $corpId,
            'event' => $event,
        ];

        $res = $this->track->createContactEmployeeTrack($data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '记录轨迹失败');
        }
    }
}
