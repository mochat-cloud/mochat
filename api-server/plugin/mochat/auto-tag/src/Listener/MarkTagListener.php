<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Codec\Json;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use MoChat\Plugin\AutoTag\Service\AutoTagRecordService;
use Psr\Container\ContainerInterface;

/**
 * 新客户打标签监听.
 *
 * @Listener
 */
class MarkTagListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * @var AutoTagContract
     */
    private $autoTagService;

    /**
     * @var AutoTagRecordService
     */
    private $autoTagRecordService;

    public function listen(): array
    {
        return [
            AddContactEvent::class,
        ];
    }

    /**
     * @param AddContactEvent $event
     */
    public function process(object $event)
    {
        $contact = $event->message;
        $this->autoTagService = $this->container->get(AutoTagContract::class);
        $this->workContactTagService = $this->container->get(WorkContactTagContract::class);
        $this->autoTagRecordService = $this->container->get(AutoTagRecordContract::class);

        // 判断是否需要打标签
        if (! $this->isNeedMarkTag($contact)) {
            return;
        }

        // 获取打标签规则
        $tagIds = $this->getMarkTagRule($contact);
        if (empty($tagIds)) {
            return;
        }

        // 打标签
        $this->workContactTagService->markTags((int) $contact['corpId'], (int) $contact['id'], (int) $contact['employeeId'], $tagIds);
    }

    /**
     * 判断是否需要打标签.
     *
     * @return bool
     */
    private function isNeedMarkTag(array $contact)
    {
        return true;
    }

    /**
     * 获取打标签规则.
     *
     * @param array $contact 客户
     */
    private function getMarkTagRule(array $contact): array
    {
        $data = [];

        // 查询企业全部标签【状态:开启-1 类型：分时段打标签-3】
        $autoTag = $this->autoTagService->getAutoTagByCorpIdStatus([$contact['corpId']], 3, 1, ['id', 'tag_rule', 'employees']);
        if (empty($autoTag)) {
            return $data;
        }

        // 客户标签
        $tagIds = [];
        foreach ($autoTag as $item) {
            if (! $this->hasCurrentEmployee($contact['employeeId'], $item['employees'])) {
                continue;
            }

            $currentTagIds = $this->getNeedMarkTagIds($contact, (int) $item['id'], $item['tagRule']);
            if (empty($currentTagIds)) {
                continue;
            }

            $tagIds = array_merge($tagIds, $currentTagIds);
        }

        if (empty($tagIds)) {
            return $data;
        }

        $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['wx_contact_tag_id']);

        if (empty($tagList)) {
            return $data;
        }

        $data = array_merge($data, array_column($tagList, 'wxContactTagId'));
        empty($data) || $data = array_merge(array_unique($data));

        return $data;
    }

    /**
     * TODO 增加选择部门支持
     * 判断是否是当前员工的规则.
     *
     * @return bool
     */
    private function hasCurrentEmployee(int $employeeId, string $employees)
    {
        $employees = Json::decode($employees);

        if (empty($employees)) {
            return false;
        }

        $employees = array_map(function ($employee) {
            return (int) $employee;
        }, $employees);

        return in_array($employeeId, $employees);
    }

    /**
     * 获取需要打标签的id.
     *
     * @return array
     */
    private function getNeedMarkTagIds(array $contact, int $autoTagId, string $tagRules)
    {
        $tagIds = [];
        $tagRules = Json::decode($tagRules);

        if (empty($tagRules)) {
            return $tagIds;
        }

        foreach ($tagRules as $tagKey => $tagRule) {
            // 时:分
            if (date('H:i') < $tagRule['start_time'] || date('H:i') > $tagRule['end_time']) {
                continue;
            }

            // 月
            if ($tagRule['time_type'] === 3 && ! in_array((int) date('d'), $tagRule['schedule'], true)) {
                continue;
            }

            // 周
            if ($tagRule['time_type'] === 2 && ! in_array((int) date('w'), $tagRule['schedule'], true)) {
                continue;
            }

            // 天
            if ($tagRule['time_type'] === 1) {
                // 无需处理
            }

            $currentTagIds = array_column($tagRule['tags'], 'tagid');
            if (empty($currentTagIds)) {
                continue;
            }

            $tagRuleId = $tagKey + 1;
            $this->createAutoTagRecord($contact, $autoTagId, $tagRuleId, $tagIds);

            $tagIds = array_merge($tagIds, $currentTagIds);
        }

        return $tagIds;
    }

    private function createAutoTagRecord(array $contact, int $autoTagId, int $tagRuleId, array $tagIds)
    {
        if (empty($tagIds)) {
            return;
        }

        $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['name']);
        $createData = [
            'auto_tag_id' => $autoTagId,
            'contact_id' => $contact['id'],
            'tag_rule_id' => $tagRuleId,
            'tags' => Json::encode(array_column($tagList, 'name')),
            'trigger_count' => 1,
            'wx_external_userid' => $contact['wxExternalUserid'],
            'employee_id' => $contact['employeeId'],
            'corp_id' => $contact['corpId'],
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->autoTagRecordService->createAutoTagRecord($createData);
    }
}
