<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactSop\QueueService\ContactSopPush;
use MoChat\Plugin\ContactSop\Service\ContactSopLogService;
use MoChat\Plugin\ContactSop\Service\ContactSopService;

/**
 * 添加企业客户事件.
 *
 * @Listener
 */
class AddContactListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContactSopLogService
     */
    protected $contactSopLogService;

    /**
     * @Inject
     * @var ContactSopService
     */
    protected $contactSopService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ContactSopPush
     */
    protected $contactSopPush;

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
        $employee = $this->workEmployeeService->getWorkEmployeeById($contact['employeeId']);
        if (empty($employee)) {
            return;
        }

        // 个人sop 好友回调倒计时提示恢复客户指定消息
        $list = $this->contactSopService->getContactSopByCorpIdState([$contact['corpId']]);
        $rightList = [];
        foreach ($list as $item) {
            if (in_array($contact['employeeId'], json_decode($item['employeeIds']))) {
                $rightList[] = $item;
            }
        }

        if (empty($rightList)) {
            return;
        }

        foreach ($rightList as $item) {
            $taskList = json_decode($item['setting'], true, 512, JSON_THROW_ON_ERROR);
            $currentTime = time();
            $currentDayZeroTime = strtotime(date('Y-m-d') . ' 00:00:00');
            foreach ($taskList as $task) {
                $delay = 0;
                if ((int) $task['time']['type'] === 0) {
                    // 1时 30分
                    $delay += (int) $task['time']['data']['first'] * 3600;
                    $delay += (int) $task['time']['data']['last'] * 60;
                } else {
                    // 1天 11:30
                    $delayDate = date('Y-m-d', $currentDayZeroTime + (int) $task['time']['data']['first'] * 86400);
                    $delayTime = strtotime($delayDate . ' ' .  $task['time']['data']['last'] . ':00');
                    $delay = $delayTime - $currentTime;
                }

                // 添加触发记录
                $sopLogId = $this->contactSopLogService->createContactSopLog([
                    'corp_id' => $contact['corpId'],
                    'contact_sop_id' => $item['id'],
                    'employee' => $employee['wxUserId'],
                    'contact' => $contact['wxExternalUserid'],
                    'task' => json_encode($task, JSON_THROW_ON_ERROR),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // 启动倒计时提醒
                $this->contactSopPush->push([
                    'corpId' => $contact['corpId'],
                    'contactSopId' => $item['id'],
                    'contactSopLogId' => $sopLogId,
                    'employeeWxId' => $employee['wxUserId'],
                    'contactName' => $contact['name'],
                    'sopName' => $item['name'],
                ], $delay);
            }
        }
    }
}
