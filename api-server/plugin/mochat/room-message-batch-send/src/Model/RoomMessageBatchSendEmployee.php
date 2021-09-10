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
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $batchId 客户群消息群发id （mc_contact_message_batch_send.id)
 * @property int $employeeId 员工id （mc_work_employee.id)
 * @property string $wxUserId 微信userId （mc_work_employee.wx_user_id)
 * @property int $sendRoomTotal 发送群数量
 * @property string $content 群发消息内容
 * @property string $errCode 返回码
 * @property string $errMsg 对返回码的文本描述内容
 * @property string $msgId 企业群发消息的id，可用于获取群发消息发送结果
 * @property string $sendTime 发送时间
 * @property string $lastSyncTime 最后一次同步结果时间
 * @property int $status 状态（0-未发送，1-已全部发送，2-已部分发送）
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 */
class RoomMessageBatchSendEmployee extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_message_batch_send_employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'batch_id', 'employee_id', 'wx_user_id', 'send_room_total', 'content', 'err_code', 'err_msg', 'msg_id', 'send_time', 'last_sync_time', 'status', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'batch_id' => 'integer', 'employee_id' => 'integer', 'send_room_total' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function getContentAttribute($value)
    {
        return empty($value) ? null : json_decode($value, true);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
