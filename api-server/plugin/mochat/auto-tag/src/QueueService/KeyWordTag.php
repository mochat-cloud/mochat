<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkContact\QueueService\UpdateContactTagApply;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Constants\MsgType;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use MoChat\Plugin\SensitiveWord\Contract\SensitiveWordMonitorContract;

/**
 * 关键词打标签-会话存档信息
 * Class KeyWordTag.
 */
class KeyWordTag
{
    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @Inject
     * @var SensitiveWordMonitorContract
     */
    protected $monitorService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @var UpdateContactTagApply
     */
    protected $contactTagService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * 客户表.
     * @Inject
     * @var WorkContactContract
     */
    private $workContactService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

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
     * @AsyncQueueMessage(pool="chat")
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $contact_list, array $autoTag, int $corpId): void
    {
        ## 记录业务日志-1
        $this->logger->info(sprintf('%s [%s] %s', '关键词打标签异步调用开始', date('Y-m-d H:i:s'), json_encode($contact_list)));
        ## 校验参数
        if (empty($contact_list)) {
            return;
        }

        foreach ($contact_list as $key => $val) {
            $today_data = make(WorkMessageContract::class, [$corpId])->getWorkMessagesByCorpIdActionFrom($corpId, $val['wxExternalUserid'], date('Y-m-d'));
            $week_data = make(WorkMessageContract::class, [$corpId])->getWorkMessagesByCorpIdActionFrom($corpId, $val['wxExternalUserid'], date('Y-m-d', (time() - ((date('w', time()) == 0 ? 7 : date('w', time())) - 1) * 24 * 3600)));
            $month_data = make(WorkMessageContract::class, [$corpId])->getWorkMessagesByCorpIdActionFrom($corpId, $val['wxExternalUserid'], date('Y-m-01', time()));
            foreach ($autoTag as $tag) {
                $rule = json_decode($tag['tagRule'], true, 512, JSON_THROW_ON_ERROR);
                foreach ($rule as $keyRule => $valRule) {
                    if ($valRule['time_type'] === 1) {
                        ## 过滤已打标签
                        $contactRecord = $this->autoTagRecordService->getAutoTagRecordByCorpIdWxExternalUseridAutoTagId((int) $corpId, $val['wxExternalUserid'], $tag['id'], $keyRule + 1, ['id', 'trigger_count', 'status', 'tags']);
                        if (! empty($contactRecord) && current($contactRecord)['status'] === 1) {
                            continue;
                        }
                        ## 敏感词触发统计
                        $updateWords = $this->updateWords($today_data, $tag);
                        if (empty($updateWords)) {
                            continue;
                        }
                        if ($updateWords[$val['wxExternalUserid']]['num'] >= $valRule['trigger_count']) {
                            ## 客户
                            $this->params['contactId'] = $this->getContactId($corpId, $val['wxExternalUserid']);
                            $this->params['contactWxExternalUserid'] = $val['wxExternalUserid'];
                            ## 员工
                            $this->params['employeeId'] = $this->getEmployeeId($corpId, $updateWords[$val['wxExternalUserid']]['employee']);
                            $this->params['employeeWxUserId'] = $updateWords[$val['wxExternalUserid']]['employee'];
                            ## 修改标签并记录轨迹
                            $this->updateTag(array_column($valRule['tags'], 'tagid'), $updateWords[$val['wxExternalUserid']]['num'], $tag['id'], $keyRule + 1, $updateWords[$val['wxExternalUserid']]['keyword'], $corpId);
                        }
                    }
                    if ($valRule['time_type'] === 2) {
                        ## 过滤已打标签
                        $contactRecord = $this->autoTagRecordService->getAutoTagRecordByCorpIdWxExternalUseridAutoTagId((int) $corpId, $val['wxExternalUserid'], $tag['id'], $keyRule + 1, ['id', 'trigger_count', 'status', 'tags']);
                        if (! empty($contactRecord) && current($contactRecord)['status'] === 1) {
                            continue;
                        }
                        ## 敏感词触发统计
                        $updateWords = $this->updateWords($week_data, $tag);
                        if (empty($updateWords)) {
                            continue;
                        }
                        if ($updateWords[$val['wxExternalUserid']]['num'] >= $valRule['trigger_count']) {
                            ## 客户
                            $this->params['contactId'] = $this->getContactId((int) $corpId, $val['wxExternalUserid']);
                            $this->params['contactWxExternalUserid'] = $val['wxExternalUserid'];
                            ## 员工
                            $this->params['employeeId'] = $this->getEmployeeId((int) $corpId, $updateWords[$val['wxExternalUserid']]['employee']);
                            $this->params['employeeWxUserId'] = $updateWords[$val['wxExternalUserid']]['employee'];
                            ## 修改标签并记录轨迹
                            $this->updateTag(array_column($valRule['tags'], 'tagid'), $updateWords[$val['wxExternalUserid']]['num'], $tag['id'], $keyRule + 1, $updateWords[$val['wxExternalUserid']]['keyword'], $corpId);
                        }
                    }
                    if ($valRule['time_type'] === 3) {
                        ## 过滤已打标签
                        $contactRecord = $this->autoTagRecordService->getAutoTagRecordByCorpIdWxExternalUseridAutoTagId((int) $corpId, $val['wxExternalUserid'], $tag['id'], $keyRule + 1, ['id', 'trigger_count', 'status', 'tags']);
                        if (! empty($contactRecord) && current($contactRecord)['status'] === 1) {
                            continue;
                        }
                        ## 敏感词触发统计
                        $updateWords = $this->updateWords($month_data, $tag);
                        if ($updateWords[$val['wxExternalUserid']]['num'] >= $valRule['trigger_count']) {
                            ## 客户
                            $this->params['contactId'] = $this->getContactId((int) $corpId, $val['wxExternalUserid']);
                            $this->params['contactWxExternalUserid'] = $val['wxExternalUserid'];
                            ## 员工
                            $this->params['employeeId'] = $this->getEmployeeId((int) $corpId, $updateWords[$val['wxExternalUserid']]['employee']);
                            $this->params['employeeWxUserId'] = $updateWords[$val['wxExternalUserid']]['employee'];
                            ## 修改标签并记录轨迹
                            $this->updateTag(array_column($valRule['tags'], 'tagid'), $updateWords[$val['wxExternalUserid']]['num'], $tag['id'], $keyRule + 1, $updateWords[$val['wxExternalUserid']]['keyword'], $corpId);
                        }
                    }
                }
            }
        }
    }

    private function updateWords($data, $tag): array
    {
        ## 敏感词触发统计
        $updateWords = [];
        foreach ($data as $message) {
            is_array($message) || $message = json_decode(json_encode($message), true);
            ## 过滤-非文本消息
            if ($message['msg_type'] != MsgType::TEXT) {
                continue;
            }
            ## 过滤空消息
            $messageContent = json_decode($message['content'], true);
            if (! isset($messageContent['content']) || empty($messageContent['content'])) {
                continue;
            }

            ## 模糊匹配
            if (! empty($tag['fuzzyMatchKeyword'])) {
                foreach (explode(',', $tag['fuzzyMatchKeyword']) as $fuzzyMatchKeyword) {
                    isset($updateWords[$message['from']]) || $updateWords[$message['from']] = [
                        'num' => 0,
                        'keyword' => $fuzzyMatchKeyword,
                        'employee' => json_decode($message['tolist'], true)[0],
                    ];
                    if (stripos($messageContent['content'], $fuzzyMatchKeyword) !== false && in_array(json_decode($message['tolist'], true)[0], explode(',', $tag['employees']), true)) {
                        ++$updateWords[$message['from']]['num'];
                        $updateWords[$message['from']]['keyword'] = $fuzzyMatchKeyword;
                        $updateWords[$message['from']]['employee'] = json_decode($message['tolist'], true)[0];
                    }
                }
            }
            ## 精准匹配
            if (! empty($tag['exactMatchKeyword'])) {
                foreach (explode(',', $tag['exactMatchKeyword']) as $exactMatchKeyword) {
                    isset($updateWords[$message['from']]) || $updateWords[$message['from']] = [
                        'num' => 0,
                        'keyword' => $exactMatchKeyword,
                        'employee' => json_decode($message['tolist'], true)[0],
                    ];
                    if ($messageContent['content'] === $exactMatchKeyword && in_array(json_decode($message['tolist'], true)[0], explode(',', $tag['employees']), true)) {
                        ++$updateWords[$message['from']]['num'];
                        $updateWords[$message['from']]['keyword'] = $exactMatchKeyword;
                        $updateWords[$message['from']]['employee'] = json_decode($message['tolist'], true)[0];
                    }
                }
            }
        }
        return $updateWords;
    }

    /**
     * 获取客户ID.
     */
    private function getContactId(int $corpId, string $wxExternalUserid): int
    {
        $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corpId, $wxExternalUserid, ['id']);
        return empty($contact) ? 0 : $contact['id'];
    }

    /**
     * @param int $corpId 企业ID
     * @param string $wxUserId 微信用户ID
     */
    private function getEmployeeId(int $corpId, string $wxUserId): int
    {
        $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserId((string) $corpId, $wxUserId, ['id']);
        return empty($employee) ? 0 : current($employee)['id'];
    }

    private function updateTag(array $tagArr, int $trigger_count, int $tag_id, int $tag_rule_id, string $keyword, int $corpId): array
    {
        $addWxTag = [];
        //查询员工对客户所打标签
        $contactTagPivot = $this->workContactTagPivotService->getWorkContactTagPivotsByOtherId($this->params['contactId'], $this->params['employeeId'], ['contact_tag_id']);
        //若客户已有标签
        if (! empty($contactTagPivot)) {
            //已有标签id
            $alreadyTagIds = array_column($contactTagPivot, 'contactTagId');
            //本次修改标签id
            $currentTagIds = $tagArr;
            //本次修改的标签id与已有标签id取差集 则为本次需要添加的标签
            $tagDiffTwo = array_diff($currentTagIds, $alreadyTagIds);
            if (! empty($tagDiffTwo)) {
                $data = [];
                foreach ($tagDiffTwo as $val) {
                    $data[] = [
                        'contact_id' => $this->params['contactId'],
                        'employee_id' => $this->params['employeeId'],
                        'contact_tag_id' => $val,
                    ];
                }

                //查询标签信息
                $tagInfo = $this->getWorkContactTag($tagDiffTwo);
                //需要同步添加的微信标签
                $addWxTag = $tagInfo['wxTagIds'];
                //标签名称
                $addTagName = $tagInfo['tagName'];

                //添加本地客户标签
                $res = $this->workContactTagPivotService->createWorkContactTagPivots($data);
                if ($res != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
                }
                //添加关键词打标签客户记录
                $createMonitors = [
                    'auto_tag_id' => $tag_id,
                    'contact_id' => $this->params['contactId'],
                    'tag_rule_id' => $tag_rule_id,
                    'wx_external_userid' => $this->params['contactWxExternalUserid'],
                    'employee_id' => $this->params['employeeId'],
                    'keyword' => $keyword,
                    'tags' => json_encode($addTagName),
                    'corp_id' => $corpId,
                    'trigger_count' => $trigger_count,
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $tagRes = $this->autoTagRecordService->createAutoTagRecord($createMonitors);
                if ($tagRes != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加关键词打标签客户记录失败');
                }
            }
        } else {  //若客户没有标签 直接添加
            //查询标签信息
            $tagInfo = $this->getWorkContactTag($tagArr);
            //需要同步添加的微信标签id
            $addWxTag = $tagInfo['wxTagIds'];
            //标签名称
            $addTagName = $tagInfo['tagName'];

            $data = [];
            foreach ($tagArr as $val) {
                $data[] = [
                    'contact_id' => $this->params['contactId'],
                    'employee_id' => $this->params['employeeId'],
                    'contact_tag_id' => $val,
                ];
            }
            //添加客户标签
            $res = $this->workContactTagPivotService->createWorkContactTagPivots($data);

            if ($res != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
            }
            //添加关键词打标签客户记录
            $createMonitors = [
                'auto_tag_id' => $tag_id,
                'contact_id' => $this->params['contactId'],
                'tag_rule_id' => $tag_rule_id,
                'wx_external_userid' => $this->params['contactWxExternalUserid'],
                'employee_id' => $this->params['employeeId'],
                'keyword' => $keyword,
                'tags' => json_encode($addTagName),
                'corp_id' => $corpId,
                'trigger_count' => $trigger_count,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $tagRes = $this->autoTagRecordService->createAutoTagRecord($createMonitors);
            if ($tagRes != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加关键词打标签客户记录失败');
            }
        }

        //同步到企业微信
        $tagData = [];
        if (! empty($addWxTag)) {
            $tagData['add_tag'] = $addWxTag;
        }

        if (! empty($tagData)) {
            //同步到企业微信
            $this->contactTagService = make(UpdateContactTagApply::class);
            $params = [
                'add_tag' => $addWxTag,
                'userid' => $this->params['employeeWxUserId'],
                'external_userid' => $this->params['contactWxExternalUserid'],
            ];
            $this->contactTagService->handle($params, user()['corpIds'][0]);
        }

        //记录标签轨迹
        if (! empty($addTagName)) {
            $content = '系统对该客户打标签';
            foreach ($addTagName as $key => $value) {
                if ($key != count($addTagName) - 1) {
                    $content .= '【' . $value . '】、';
                } else {
                    $content .= '【' . $value . '】';
                }
            }
            $this->recordTrack($content, Event::TAG);
        }
        return [];
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
     */
    private function recordTrack($content, $event)
    {
        $data = [
            'employee_id' => $this->params['employeeId'],
            'contact_id' => $this->params['contactId'],
            'content' => $content,
            'corp_id' => user()['corpIds'][0],
            'event' => $event,
        ];

        $res = $this->track->createContactEmployeeTrack($data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '记录轨迹失败');
        }
    }
}
