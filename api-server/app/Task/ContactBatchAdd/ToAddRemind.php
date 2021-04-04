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
use App\Logic\ContactBatchAdd\NoticeEmployeeLogic;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="ToAddRemind", rule="* * * * *", callback="execute", singleton=true, memo="未添加的客户自动提醒员工")
 */
class ToAddRemind
{
    /**
     * @Inject
     * @var ContactBatchAddConfigServiceInterface
     */
    protected $contactBatchAddConfigService;

    /**
     * @Inject
     * @var NoticeEmployeeLogic
     */
    protected $noticeEmployeeLogic;

    public function execute(): void
    {
        $data = $this->contactBatchAddConfigService->getContactBatchAddConfigOptionWhere([
            ['undone_status', '=', 1],
            ['undone_reminder_time', '>=', date('H:i:0')],
            ['undone_reminder_time', '<=', date('H:i:59')],
        ]);
        foreach ($data as $item) {
            $this->noticeEmployeeLogic->handle($item);
        }
    }
}
