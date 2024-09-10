<?php
/**
* ItemModel.php - Model file
*
* This file is part of the Item component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Models;

use App\Yantrana\Base\BaseModel;

class ItemModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'items';

    /**
     * @var array - The attributes that should be casted to native types..
     */
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'type' => 'integer',
        'normal_price' => 'float',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
