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
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;

trait UpdateTrait
{
    /**
     * @Inject
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * 处理空客户id数据.
     */
    protected function updateContactId(): void
    {
        $recordList = $this->autoTagRecordService->getAutoTagRecord(['id', 'corp_id', 'wx_external_userid']);
        foreach ($recordList as $item) {
            $contact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId((int) $item['corpId'], $item['wxExternalUserid'], ['id']);
            $contactId = empty($contact) ? 0 : $contact['id'];
            $this->autoTagRecordService->updateAutoTagRecordById($item['id'], ['contact_id' => $contactId]);
        }
    }
}
