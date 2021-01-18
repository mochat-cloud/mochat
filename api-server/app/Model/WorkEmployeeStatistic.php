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
use Hyperf\Database\Model\SoftDeletes;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property int $employeeId
 * @property int $newApplyCnt
 * @property int $newContactCnt
 * @property int $chatCnt
 * @property int $messageCnt
 * @property int $replyPercentage
 * @property int $avgReplyTime
 * @property int $negativeFeedbackCnt
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkEmployeeStatistic extends AbstractModel
{
    use SoftDeletes;
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_employee_statistic';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'employee_id' => 'integer', 'new_apply_cnt' => 'integer', 'new_contact_cnt' => 'integer', 'chat_cnt' => 'integer', 'message_cnt' => 'integer', 'reply_percentage' => 'integer', 'avg_reply_time' => 'integer', 'negative_feedback_cnt' => 'integer', 'syn_time' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
