<?php

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\FinancialTransaction\Models\FinancialTransaction;
use App\Yantrana\Components\Item\Models\UserItemModel;

class CreditWalletTransaction extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'credit_wallet_transactions';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        '_id' => 'integer',
        'status' => 'integer',
        'financial_transactions__id' => 'integer',
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [];

    /**
     * Get user gift transaction data.
     */
    public function getUserFinancialTransaction()
    {
        return $this->hasOne(FinancialTransaction::class, '_id', 'financial_transactions__id');
    }

    /**
     * Get user gift transaction data.
     */
    public function getUserGiftTransaction()
    {
        return $this->hasOne(UserGiftModel::class, 'credit_wallet_transactions__id', '_id');
    }

    /**
     * Get user sticker transaction data.
     */
    public function getUserStickerTransaction()
    {
        return $this->hasOne(UserItemModel::class, 'credit_wallet_transactions__id', '_id');
    }

    /**
     * Get user profile boost transaction data.
     */
    public function getUserBoostTransaction()
    {
        return $this->hasOne(ProfileBoost::class, 'credit_wallet_transactions__id', '_id');
    }

    /**
     * Get user subscription transaction data.
     */
    public function getUserSubscriptionTransaction()
    {
        return $this->hasOne(UserSubscription::class, 'credit_wallet_transactions__id', '_id');
    }
}
