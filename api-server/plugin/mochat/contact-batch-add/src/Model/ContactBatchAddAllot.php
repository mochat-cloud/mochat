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
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $importId
 * @property int $employeeId
 * @property int $type
 * @property int $operateId
 * @property \Carbon\Carbon $createdAt
 */
class ContactBatchAddAllot extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_batch_add_allot';

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
    protected $casts = ['id' => 'integer', 'import_id' => 'integer', 'employee_id' => 'integer', 'type' => 'integer', 'operate_id' => 'integer', 'created_at' => 'datetime'];
}
