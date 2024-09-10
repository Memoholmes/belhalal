<?php

namespace App\Yantrana\Components\FinancialTransaction\Models;

use App\Yantrana\Base\BaseModel;

class FinancialTransaction extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'financial_transactions';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id' => 'integer',
        'status' => 'integer',
        '__data' => 'array',
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'rawPaymentData' => 'array',
            'packageName' => 'string',
        ],
    ];
}
