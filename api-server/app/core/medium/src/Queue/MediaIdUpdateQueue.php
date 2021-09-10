<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Queue;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\App\Medium\Logic\Medium;

/**
 * 媒体id更新.
 */
class MediaIdUpdateQueue
{
    /**
     * @AsyncQueueMessage(pool="file")
     */
    public function handle(int $corpId, array $mediumIds): void
    {
        $mediaLogic = make(Medium::class);
        $mediaLogic->getWxMediumId($mediumIds, $corpId);
    }
}
