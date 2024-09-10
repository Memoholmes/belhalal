<?php
/**
* Pages.php - Model file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Pages\Models;

use App\Yantrana\Base\BaseModel;

class PageModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'pages';

    /**
     * @var array - The attributes that should be casted to native types..
     */
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'add_to_menu' => 'integer',
        'type' => 'integer',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [];
}
