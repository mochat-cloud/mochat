<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\RoomSop\Contract\RoomSopContract;

/**
 * Class DestroyLogic.
 */
class DestroyLogic
{
    /**
     * @Inject
     * @var RoomSopContract
     */
    protected $roomSopService;

    /**
     * @param $params
     * @return bool
     */
    public function handle($params)
    {
        return $this->roomSopService->deleteRoomSop((int) $params['id']) ? true : false;
    }
}
