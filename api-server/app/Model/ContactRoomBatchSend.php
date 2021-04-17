<?php

declare (strict_types=1);
namespace App\Model;

use MoChat\Framework\Model\AbstractModel;
/**
 */
class ContactRoomBatchSend extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_room_batch_send';
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
    protected $casts = [];
}