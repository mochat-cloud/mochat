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
use MoChat\Framework\Model\AbstractModel;

/**
 * @property int $id
 * @property int $corpId
 * @property string $wxAgentId
 * @property string $wxSecret
 * @property string $name
 * @property string $squareLogoUrl
 * @property string $description
 * @property int $close
 * @property string $redirectDomain
 * @property int $reportLocationFlag
 * @property int $isReportenter
 * @property string $homeUrl
 * @property \Carbon\Carbon $createdAt
 * @property \Carbon\Carbon $updatedAt
 * @property string $deletedAt
 */
class WorkAgent extends AbstractModel
{
    use CamelCase;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_agent';

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
    protected $casts = ['id' => 'integer', 'corp_id' => 'integer', 'close' => 'integer', 'report_location_flag' => 'integer', 'is_reportenter' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
