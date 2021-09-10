<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Task;

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Medium\Queue\MediaIdUpdateQueue;

/**
 * @Crontab(name="mediaIdUpdate", rule="*\/5 * * * *", callback="execute", singleton=true, memo="素材库media_id更新")
 */
class MediaIdUpdate
{
    /**
     * @Inject
     * @var CorpContract
     */
    private $corpService;

    /**
     * @Inject
     * @var MediumContract
     */
    private $mediaService;

    /**
     * @Inject
     * @var MediaIdUpdateQueue
     */
    private $mediaIdUpdateQueue;

    public function execute(): void
    {
        // 循环企业
        $corpConfig = $this->corpService->getCorps(['id']);

        foreach ($corpConfig as $corp) {
            $mediumIds = $this->mediaService->getMediaByUpdatingMediaId((int) $corp['id'], ['id']);
            if (empty($mediumIds)) {
                continue;
            }

            $this->mediaIdUpdateQueue->handle((int) $corp['id'], array_column($mediumIds, 'id'));
        }
    }
}
