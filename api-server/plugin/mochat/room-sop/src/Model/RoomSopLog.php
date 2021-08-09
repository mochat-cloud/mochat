<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomSop\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property int $roomSopId
 * @property int $roomId
 * @property int $state
 * @property string $employee
 * @property string $contact
 * @property string $task
 * @property \Carbon\Carbon $createdAt
 */
class RoomSopLog extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_sop_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'rom_sop_id' => 'integer', 'room_id' => 'integer', 'state' => 'integer', 'created_at' => 'datetime'];
}
