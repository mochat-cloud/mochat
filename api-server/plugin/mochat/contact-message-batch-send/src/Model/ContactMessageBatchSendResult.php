<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $batchId 客户消息群发id （mc_contact_message_batch_send.id)
 * @property int $employeeId 员工id （mc_work_employee.id)
 * @property int $contactId 客户表id（work_contact.id）
 * @property string $externalUserId 外部联系人userid
 * @property string $userId 企业服务人员的userid
 * @property int $status 发送状态 0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失败
 * @property int $sendTime 发送时间，发送状态为1时返回
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 */
class ContactMessageBatchSendResult extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_message_batch_send_result';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'batch_id', 'employee_id', 'contact_id', 'external_user_id', 'user_id', 'status', 'send_time', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'batch_id' => 'integer', 'employee_id' => 'integer', 'contact_id' => 'integer', 'status' => 'integer', 'send_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
