<?php
/**
* ActivityLog.php - Model file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class ActivityLog extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'activity_logs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The generate UID or not.
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $isGenerateUID = false;

    /**
     * @var array - The attributes that should be casted to native types..
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        '__data' => 'string',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = ['created_at', 'user_id', '__data'];
}
