<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use Hyperf\Database\Model\SoftDeletes;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $corpId
 * @property string $activeName
 * @property int $match_corp_id
 * @property string $service_employees
 * @property int $auto_pass
 * @property int $auto_add_tag
 * @property string $contact_tags
 * @property string $end_time
 * @property int $qr_code_invalid
 * @property string $tasks
 * @property int $new_friend
 * @property int $delete_invalid
 * @property int $receive_prize
 * @property string $receive_prize_employees
 * @property string $receive_links
 * @property int $create_user_id
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkFissionPush extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_fission_push';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer',  'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
