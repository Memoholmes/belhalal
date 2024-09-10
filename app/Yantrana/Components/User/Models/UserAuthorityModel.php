<?php
/**
* UserAuthorityModel.php - Model file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class UserAuthorityModel extends BaseModel
{
    /**
     * @var  string - The database table used by the model.
     */
    protected $table = 'user_authorities';

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;

    /**
     * @var  array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '_id' => 'integer',
        'status' => 'integer',
        'users__id' => 'integer',
        'user_roles__id' => 'integer',
        '__permissions' => 'array',
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__permissions' => [
            'allow' => 'array',
            'deny' => 'array',
        ],
    ];

    /**
     * @var  array - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
