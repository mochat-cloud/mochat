<?php

declare (strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochatcloud/mochat/blob/master/LICENSE
 */
namespace App\Model;

use MoChat\Framework\Model\AbstractModel;
use Hyperf\Database\Model\Concerns\CamelCase;
/**
 * @property int $id 
 * @property int $corpId 
 * @property string $title 
 * @property string $uploadAt 
 * @property string $allotEmployee 
 * @property string $tags 
 * @property int $importNum 
 * @property int $addNum 
 * @property string $fileName 
 * @property string $fileUrl 
 * @property \Carbon\Carbon $createdAt 
 * @property \Carbon\Carbon $updatedAt 
 * @property string $deletedAt 
 */
class ContactBatchAddImportRecord extends AbstractModel
{
    use CamelCase;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_batch_add_import_record';
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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'import_num' => 'integer', 'add_num' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}