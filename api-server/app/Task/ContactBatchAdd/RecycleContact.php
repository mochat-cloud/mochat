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
use App\Logic\ContactBatchAdd\RecycleContactLogic;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="RecycleContact", rule="0 * * * *", callback="execute", singleton=true, memo="添加客户未通过超时回收客户")
 */
class RecycleContact
{
    /**
     * @Inject
     * @var ContactBatchAddConfigServiceInterface
     */
    protected $contactBatchAddConfigService;

    /**
     * @Inject
     * @var RecycleContactLogic
     */
    protected $recycleContactLogic;

    public function execute(): void
    {
        $data = $this->contactBatchAddConfigService->getContactBatchAddConfigOptionWhere([
            ['recycle_status', '=', 1],
        ]);
        foreach ($data as $item) {
            $this->recycleContactLogic->handle($item);
        }
    }
}
