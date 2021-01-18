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

use App\Model\Traits\WorkMessageTrait;
use Hyperf\Database\Model\Concerns\CamelCase;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property int $seq
 * @property string $msgid
 * @property int $action
 * @property string $from
 * @property string $tolist
 * @property string $roomId
 * @property int $msgtime
 * @property string $msgtype
 * @property string $content
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkMessage extends AbstractModel
{
    use CamelCase;
    use WorkMessageTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_message';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'seq' => 'integer', 'action' => 'integer', 'msgtime' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
