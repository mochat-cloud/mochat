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


use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Utils\Codec\Json;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Event\CreateRoomMemberEvent;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\AutoContactTag;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use Psr\Container\ContainerInterface;

/**
 * 客户群创建事件
 * 有新增客户群时，回调该事件。收到该事件后，企业可以调用获取客户群详情接口获取客户群详情。
 * @Listener
 */
class CreateRoomMemberListener implements ListenerInterface
{

    use AutoContactTag;

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function listen(): array
    {
        return [
            CreateRoomMemberEvent::class,
        ];
    }
    /**
     * @param CreateRoomMemberEvent $event
     */
    public function process(object $event)
    {
        try {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
            $this->workEmployeeService = $this->container->get(WorkEmployeeContract::class);
            $this->workContactService = $this->container->get(WorkContactContract::class);
            $this->workContactEmployeeService = $this->container->get(WorkContactEmployeeContract::class);
            $this->autoTagService = $this->container->get(AutoTagContract::class);
            $this->autoTagRecordService = $this->container->get(AutoTagRecordContract::class);

            $this->logger->info('[自动打标签]客户入群行为打标签开始');
            $corpId = $event->message['corpId'];
            $rooms = $event->message['rooms'];

            if (empty($rooms)) {
                return [];
            }
            $autoTag = $this->autoTagService->getAutoTagByTypeOnOff(2, 1, ['id', 'tag_rule', 'corp_id']);
            if (empty($autoTag)) {
                return [];
            }
            foreach ($autoTag as $auto) {
                // 2 客户入群群聊id
                foreach ($rooms as $room) {
                    // 客户0跳出循环
                    $contactId = (int) $room['contact_id'];
                    if ($contactId === 0) {
                        continue;
                    }
                    if (empty($auto['tagRule'])) {
                        continue;
                    }
                    foreach (json_decode($auto['tagRule'], true, 512, JSON_THROW_ON_ERROR) as $key => $tagRule) {
                        if (empty($tagRule) || empty($tagRule['rooms'])) {
                            continue;
                        }
                        // 空标签跳出循环
                        if (empty($tagRule['tags'])) {
                            continue;
                        }
                        $roomIds = array_column($tagRule['rooms'], 'id');
                        $tags = $tagRule['tags'];
                        if (!in_array((int) $room['room_id'], $roomIds, true)) {
                            continue;
                        }
                        $data = $this->createTagData((int)$auto['corpId'], $contactId);
                        if (empty($data)) {
                            continue;
                        }
                        $data['tagArr'] = array_column($tags, 'tagid');
                        $this->autoTag($data);
                        // 数据库操作
                        $this->createAutoTageRecord($data,$tags,$room['room_id'],$key + 1, $auto['id']);
                    }
                }
            }

            $this->logger->info('[自动打标签]客户入群行为打标签结束');
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('[自动打标签]客户入群行为打标签失败，错误信息: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        }
    }

    private function createTagData(int $corpId, int $contactId)
    {
        $data = [];
        // 员工id
        $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contactId, $corpId, ['employee_id']);
        if (empty($contactEmployee) || empty($contactEmployee['employeeId'])) {
            return $data;
        }
        // 客户
        $contact = $this->workContactService->getWorkContactById($contactId, ['wx_external_userid']);
        if (empty($contact) || empty($contact['wxExternalUserid'])) {
            return $data;
        }
        // 员工
        $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmployee['employeeId'], ['wx_user_id']);
        if (empty($employee) || empty($employee['wxUserId'])) {
            return $data;
        }
        return [
            'corpId' => $corpId,
            'contactId' => $contactId,// 客户id
            'employeeId' => $contactEmployee['employeeId'],// 员工id
            'employeeWxUserId' => $employee['wxUserId'],// 员工external_userid
            'contactWxExternalUserid' => $contact['wxExternalUserid'], // 客户external_userid
        ];
    }

    private function createAutoTageRecord(array $data, array $tags, int $roomId, int $tagRuleId, int $autoTagId)
    {
        // 数据库操作
        $record = $this->autoTagRecordService->getAutoTagRecordByCorpIdWxExternalUseridAutoTagId($data['corpId'], $data['contactWxExternalUserid'], $autoTagId, $tagRuleId, ['id', 'trigger_count']);
        $trigger_count = empty($record) ? 1 : $record['triggerCount'] + 1;
        $createMonitors = [
            'auto_tag_id' => $autoTagId,
            'contact_id' => $data['contactId'],
            'tag_rule_id' => $tagRuleId,
            'wx_external_userid' => $data['contactWxExternalUserid'],
            'employee_id' => $data['employeeId'],
            'contact_room_id' => $roomId,
            'tags' => Json::encode(array_column($tags, 'tagname'), JSON_THROW_ON_ERROR),
            'corp_id' => $data['corpId'],
            'trigger_count' => $trigger_count,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (empty($record)) {
            $this->autoTagRecordService->createAutoTagRecord($createMonitors);
        } else {
            $this->autoTagRecordService->updateAutoTagRecordById($record['id'], $createMonitors);
        }
    }
}