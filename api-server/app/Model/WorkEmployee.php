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
 * @property string $wxUserId
 * @property string $corpId
 * @property string $name
 * @property string $mobile
 * @property string $position
 * @property int $gender
 * @property string $email
 * @property string $avatar
 * @property string $thumbAvatar
 * @property string $telephone
 * @property string $alias
 * @property string $extattr
 * @property int $status
 * @property string $qrCode
 * @property string $externalProfile
 * @property string $externalPosition
 * @property string $address
 * @property string $openUserId
 * @property int $wxMainDepartmentId
 * @property int $mainDepartmentId
 * @property int $logUserId
 * @property int $contactAuth
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkEmployee extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_employee';

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
    protected $casts = ['id' => 'integer', 'gender' => 'integer', 'status' => 'integer', 'wx_main_department_id' => 'integer', 'main_department_id' => 'integer', 'log_user_id' => 'integer', 'contact_auth' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
