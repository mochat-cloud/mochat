<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property int $sensitiveWordId
 * @property string $sensitiveWordName
 * @property int $source
 * @property int $triggerId
 * @property string $triggerName
 * @property string $triggerTime
 * @property int $receiverType
 * @property int $receiverId
 * @property string $receiverName
 * @property int $workMessageId
 * @property string $chatContent
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class SensitiveWordMonitor extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sensitive_word_monitor';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'sensitive_word_id' => 'integer', 'source' => 'integer', 'trigger_id' => 'integer', 'receiver_type' => 'integer', 'receiver_id' => 'integer', 'work_message_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
