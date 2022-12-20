<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Model;

use Hyperf\Database\Model\Concerns\CamelCase;
use Hyperf\Database\Model\SoftDeletes;
use MoChat\App\WorkContact\Model\Traits\WorkUnionidExternalUseridMappingTrait;
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property string $unionid
 * @property string $openid
 * @property string $externalUserid
 * @property string $pendingId
 * @property int $subjectType
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkUnionidExternalUseridMapping extends AbstractModel
{
    use CamelCase;
    use SoftDeletes;
    use WorkUnionidExternalUseridMappingTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_unionid_external_userid_mapping';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}