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
 * @property int $parentId
 * @property string $name
 * @property int $level
 * @property string $path
 * @property string $icon
 * @property int $status
 * @property int $linkType
 * @property int $isPageMenu
 * @property string $linkUrl
 * @property int $dataPermission
 * @property int $operateId
 * @property string $operateName
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class RbacMenu extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_menu';

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
    protected $casts = ['id' => 'integer', 'parent_id' => 'integer', 'level' => 'integer', 'status' => 'integer', 'link_type' => 'integer', 'is_page_menu' => 'integer', 'data_permission' => 'integer', 'operate_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
