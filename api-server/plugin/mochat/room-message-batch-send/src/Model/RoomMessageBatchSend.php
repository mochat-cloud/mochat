<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use Hyperf\Database\Model\SoftDeletes;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId 企业表ID （mc_corp.id）
 * @property int $userId 用户ID【mc_user.id】
 * @property string $userName 用户名称【mc_user.name】
 * @property string $batchTitle 群发名称
 * @property string $content 群发消息内容
 * @property int $sendWay 发送方式（1-立即发送，2-定时发送）
 * @property string $definiteTime 定时发送时间
 * @property string $sendTime 发送时间
 * @property int $sendEmployeeTotal 发送成员数量
 * @property int $sendRoomTotal 发送群数量
 * @property int $sendTotal 已发送数量
 * @property int $notSendTotal 未发送数量
 * @property int $receivedTotal 已送达数量
 * @property int $notReceivedTotal 未送达数量
 * @property int $sendStatus 状态（0-未发送，1-已发送）
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class RoomMessageBatchSend extends AbstractModel
{
    use SoftDeletes;
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_message_batch_send';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'corp_id', 'user_id', 'user_name', 'employee_ids', 'batch_title', 'content', 'send_way', 'definite_time', 'send_time', 'send_employee_total', 'send_room_total', 'send_total', 'not_send_total', 'received_total', 'not_received_total', 'send_status', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'user_id' => 'integer', 'send_way' => 'integer', 'send_employee_total' => 'integer', 'send_room_total' => 'integer', 'send_total' => 'integer', 'not_send_total' => 'integer', 'received_total' => 'integer', 'not_received_total' => 'integer', 'send_status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function getContentAttribute($value)
    {
        return empty($value) ? null : json_decode($value, true);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getEmployeeIdsAttribute($value)
    {
        return empty($value) ? [] : json_decode($value, true);
    }

    public function setEmployeeIdsAttribute($value)
    {
        $this->attributes['employee_ids'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
