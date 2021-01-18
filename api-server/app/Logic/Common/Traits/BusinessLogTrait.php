<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Common\Traits;

use App\Contract\BusinessLogServiceInterface;
use Hyperf\Di\Annotation\Inject;

trait BusinessLogTrait
{
    /**
     * @Inject
     * @var BusinessLogServiceInterface
     */
    protected $businessLogService;

    public function getBusinessIds(array $operationIdArr, array $eventArr): array
    {
        $businessLogs = $this->businessLogService->getBusinessLogsByOperationIdsEvents($operationIdArr, $eventArr, ['business_id']);
        return empty($businessLogs) ? [] : array_unique(array_column($businessLogs, 'businessId'));
    }
}
