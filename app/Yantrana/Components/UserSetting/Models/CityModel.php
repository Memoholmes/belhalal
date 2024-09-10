<?php

/**
 * City.php - Model file
 *
 * This file is part of the UserSetting component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Models;

use App\Yantrana\Base\BaseModel;

class CityModel extends BaseModel
{
    /**
     * The custom primary key.
     *
     * @var string
     *----------------------------------------------------------------------- */
    protected $primaryKey = 'id';

    /**
     * @var  string - The database table used by the model.
     */
    protected $table = 'cities';

    /**
     * @var  array - The attributes that should be casted to native types.
     */
    protected $casts = [];

    /**
     * @var  array - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
