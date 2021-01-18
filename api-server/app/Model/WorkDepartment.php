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
 * @property int $wxDepartmentId
 * @property int $corpId
 * @property string $name
 * @property int $parentId
 * @property int $wxParentid
 * @property int $order
 * @property string $path
 * @property int $level
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkDepartment extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_department';

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
    protected $casts = ['id' => 'integer', 'wx_department_id' => 'integer', 'corp_id' => 'integer', 'parent_id' => 'integer', 'wx_parentid' => 'integer', 'order' => 'integer', 'path' => 'string', 'level' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
