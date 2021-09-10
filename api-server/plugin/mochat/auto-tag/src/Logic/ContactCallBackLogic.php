<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;

/**
 * 自动打标签 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
    /**
     * 标签.
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

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
     * @param $params
     * @throws \JsonException
     * @return array 响应数组
     */
    public function getAutoTag($params): array
    {
        $data = [
            'tags' => [],
            'content' => [],
        ];
        ## 查询企业全部标签【状态:开启】
        $autoTag = $this->autoTagService->getAutoTagByCorpIdStatus([$params['corpId']], 3, 1, ['id', 'tag_rule']);
        if (empty($autoTag)) {
            return $data;
        }
        ## 判断当前日期是否在特殊时期内
        $data = [
            'tags' => [],
            'content' => [],
        ];
        $createMonitors = [
            'auto_tag_id' => 0,
            'contact_id' => 0,
            'tag_rule_id' => 0,
            'tags' => '',
            'trigger_count' => 1,
            'wx_external_userid' => $params['contactWxExternalUserid'],
            'employee_id' => $params['wxUserId'],
            'corp_id' => $params['corpId'],
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        ## 查询企业全部标签【状态:开启】
        $autoTag = $this->autoTagService->getAutoTagByCorpIdStatus([1], 3, 1, ['id', 'tag_rule']);
        ## 客户标签
        foreach ($autoTag as $item) {
            foreach (json_decode($item['tagRule'], true, 512, JSON_THROW_ON_ERROR) as $tagKey => $tagRule) {
                ## 天
                if ($tagRule['time_type'] === 1 && date('H:i') >= $tagRule['start_time'] && date('H:i') <= $tagRule['end_time']) {
                    $tagIds = array_column($tagRule['tags'], 'tagid');
                    $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'name', 'wx_contact_tag_id']);
                    if (! empty($tagList)) {
                        $data['tags'] = array_merge($data['tags'], array_column($tagList, 'wxContactTagId'));
                        $createMonitors['auto_tag_id'] = $item['id'];
                        $createMonitors['tag_rule_id'] = $tagKey + 1;
                        $createMonitors['tags'] = json_encode(array_column($tagList, 'name'), JSON_THROW_ON_ERROR);
                    }
                }
                ## 周
                if ($tagRule['time_type'] === 2 && in_array((int) date('w'), $tagRule['schedule'], true) && date('H:i') >= $tagRule['start_time'] && date('H:i') <= $tagRule['end_time']) {
                    $tagIds = array_column($tagRule['tags'], 'tagid');
                    $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'name', 'wx_contact_tag_id']);
                    if (! empty($tagList)) {
                        $data['tags'] = array_merge($data['tags'], array_column($tagList, 'wxContactTagId'));
                        $createMonitors['auto_tag_id'] = $item['id'];
                        $createMonitors['tag_rule_id'] = $tagKey + 1;
                        $createMonitors['tags'] = json_encode(array_column($tagList, 'name'), JSON_THROW_ON_ERROR);
                    }
                }
                ## 月
                if ($tagRule['time_type'] === 3 && in_array((int) date('d'), $tagRule['schedule'], true) && date('H:i') >= $tagRule['start_time'] && date('H:i') <= $tagRule['end_time']) {
                    $tagIds = array_column($tagRule['tags'], 'tagid');
                    $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'name', 'wx_contact_tag_id']);
                    if (! empty($tagList)) {
                        $data['tags'] = array_merge($data['tags'], array_column($tagList, 'wxContactTagId'));
                        $createMonitors['auto_tag_id'] = $item['id'];
                        $createMonitors['tag_rule_id'] = $tagKey + 1;
                        $createMonitors['tags'] = json_encode(array_column($tagList, 'name'), JSON_THROW_ON_ERROR);
                    }
                }
            }
        }
        empty($data['tags']) || $data['tags'] = array_merge(array_unique($data['tags']));
        if (! empty($data['tags'])) {
            $employee = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserId((string) $createMonitors['corp_id'], $createMonitors['employee_id'], ['id']);
            $createMonitors['employee_id'] = empty($employee) ? 0 : current($employee)['id'];
            $tagRes = $this->autoTagRecordService->createAutoTagRecord($createMonitors);
            if ($tagRes != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加关键词打标签客户记录失败');
            }
        }
        return $data;
    }
}
