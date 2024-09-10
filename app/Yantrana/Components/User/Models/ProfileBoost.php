<?php

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;

class ProfileBoost extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profile_boosts';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id' => 'integer',
        'for_users__id' => 'integer',
        'credit_wallet_transactions__id' => 'integer',
    ];
}
