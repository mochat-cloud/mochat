<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Task\ContactBatchAdd;

use App\Contract\ContactBatchAddConfigServiceInterface;
use App\Logic\ContactBatchAdd\NoticeLeaderLogic;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="pendingContactRemind", rule="* * * * *", callback="execute", singleton=true, memo="未分配的客户自动提醒管理员")
 */
class PendingContactRemind
{
    /**
     * @Inject
     * @var ContactBatchAddConfigServiceInterface
     */
    protected $contactBatchAddConfigService;

    /**
     * @Inject
     * @var NoticeLeaderLogic
     */
    protected $noticeLeaderLogic;

    public function execute(): void
    {
        $data = $this->contactBatchAddConfigService->getContactBatchAddConfigOptionWhere([
            ['pending_status', '=', 1],
            ['pending_leader_id', '!=', 0],
            ['pending_reminder_time', '>=', date('H:i:0')],
            ['pending_reminder_time', '<=', date('H:i:59')],
        ]);
        foreach ($data as $item) {
            $this->noticeLeaderLogic->handle($item);
        }
    }
}
