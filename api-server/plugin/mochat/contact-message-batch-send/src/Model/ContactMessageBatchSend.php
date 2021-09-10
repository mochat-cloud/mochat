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
use Hyperf\Database\Model\SoftDeletes;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId 企业表ID （mc_corp.id）
 * @property int $userId 用户ID【mc_user.id】
 * @property string $userName 用户名称【mc_user.name】
 * @property string $filterParams 筛选客户参数
 * @property string $filterParamsDetail 筛选客户参数详情
 * @property int $sendWay 发送方式（1-立即发送，2-定时发送）
 * @property string $definiteTime 定时发送时间
 * @property string $sendTime 发送时间
 * @property int $sendEmployeeTotal 发送成员数量
 * @property int $sendContactTotal 发送客户数量
 * @property int $sendTotal 已发送数量
 * @property int $notSendTotal 未发送数量
 * @property int $receivedTotal 已送达数量
 * @property int $notReceivedTotal 未送达数量
 * @property int $receiveLimitTotal 客户接收已达上限
 * @property int $notFriendTotal 因不是好友发送失败
 * @property int $sendStatus 状态（0-未发送，1-已发送）
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 * @property ContactMessageBatchSendEmployee[]|\Hyperf\Database\Model\Collection $employees
 * @property $content
 * @property $filter_params
 * @property $filter_params_detail
 * @property ContactMessageBatchSendResult[]|\Hyperf\Database\Model\Collection $results
 */
class ContactMessageBatchSend extends AbstractModel
{
    use SoftDeletes;
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_message_batch_send';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'corp_id', 'user_id', 'user_name', 'employee_ids', 'filter_params', 'filter_params_detail', 'content', 'send_way', 'definite_time', 'send_time', 'send_employee_total', 'send_contact_total', 'send_total', 'not_send_total', 'received_total', 'not_received_total', 'receive_limit_total', 'not_friend_total', 'send_status', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'user_id' => 'integer', 'send_way' => 'integer', 'send_employee_total' => 'integer', 'send_contact_total' => 'integer', 'send_total' => 'integer', 'not_send_total' => 'integer', 'received_total' => 'integer', 'not_received_total' => 'integer', 'receive_limit_total' => 'integer', 'not_friend_total' => 'integer', 'send_status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function getFilterParamsAttribute($value)
    {
        $value = json_decode($value, true);
        return ! empty($value) ? $value : [];
    }

    public function setFilterParamsAttribute($value)
    {
        $this->attributes['filter_params'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getFilterParamsDetailAttribute($value)
    {
        $value = json_decode($value, true);
        return ! empty($value) ? $value : [];
    }

    public function setFilterParamsDetailAttribute($value)
    {
        $this->attributes['filter_params_detail'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getContentAttribute($value)
    {
        return empty($value) ? [] : json_decode($value, true);
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
