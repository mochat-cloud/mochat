<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use Hyperf\Database\Model\SoftDeletes;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $recordId
 * @property string $phone
 * @property string $uploadAt
 * @property int $status
 * @property string $addAt
 * @property int $employeeId
 * @property int $allotNum
 * @property string $remark
 * @property string $tags
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class ContactBatchAddImport extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_batch_add_import';

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
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'status' => 'integer', 'employee_id' => 'integer', 'allot_num' => 'integer', 'tags' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
