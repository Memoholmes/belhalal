<?php

/**
 * UserRole.php - Model file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;


class UserRole extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'user_roles';

    /**
     * Does it has has Entity Ownership ID
     *
     * @var bool
     *----------------------------------------------------------------------- */
    protected $hasEoId = true;

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '_id' => 'integer',
        'status' => 'integer',
        '__permissions' => 'array',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [];

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
     * Get users related to the role
     *
     * @return void
     *---------------------------------------------------------------- */
    public function users()
    {
        return $this->hasMany(UserAuthorityModel::class, 'user_roles__id', '_id');
    }
}
