<?php

/**
 * UserSpecificationModel.php - Model file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Models;

use App\Yantrana\Base\BaseModel;

class UserSpecificationModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'user_specifications';

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '_id' => 'integer',
        'users__id' => 'integer',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
