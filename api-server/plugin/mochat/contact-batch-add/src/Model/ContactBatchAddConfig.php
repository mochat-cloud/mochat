<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property int $pendingStatus
 * @property int $pendingTimeOut
 * @property string $pendingReminderTime
 * @property int $undoneStatus
 * @property int $undoneTimeOut
 * @property string $undoneReminderTime
 * @property int $recycleStatus
 * @property int $recycleTimeOut
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 */
class ContactBatchAddConfig extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_batch_add_config';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'pending_status' => 'integer', 'pending_time_out' => 'integer', 'undone_status' => 'integer', 'undone_time_out' => 'integer', 'recycle_status' => 'integer', 'recycle_time_out' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
