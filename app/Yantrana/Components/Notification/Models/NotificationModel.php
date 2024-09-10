<?php
/**
* NotificationModel.php - Model file
*
* This file is part of the Notification component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Notification\Models;

use App\Yantrana\Base\BaseModel;

class NotificationModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'notifications';

    /**
     * @var array - The attributes that should be casted to native types..
     */
    protected $casts = [
        'id' => 'integer',
        'users__id' => 'integer',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = ['status', 'users__id', 'message', 'action', 'is_read'];
}
