<?php
/**
* CreditWalletRepository.php - Repository file
*
* This file is part of the Credit Wallet User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\FinancialTransaction\Models\FinancialTransaction;
use App\Yantrana\Components\User\Models\CreditWalletTransaction;

class CreditWalletRepository extends BaseRepository
{
    /**
     * fetch user wallet transaction list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchUserWalletTransactionList()
    {
        $dataTableConfig = [
            'searchable' => [],
        ];

        return CreditWalletTransaction::with('getUserGiftTransaction', 'getUserStickerTransaction', 'getUserBoostTransaction', 'getUserSubscriptionTransaction', 'getUserFinancialTransaction')
            ->select(
                '_id',
                '_uid',
                'created_at',
                'credits',
                'financial_transactions__id',
                'credit_type'
            )
            ->where('credit_wallet_transactions.users__id', getUserID())
            ->dataTables($dataTableConfig)
            ->toArray();
    }

    /**
     * fetch api user wallet transaction list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchApiUserWalletTransactionList()
    {
        $dataTableConfig = [
            'searchable' => [],
        ];

        return CreditWalletTransaction::with('getUserGiftTransaction', 'getUserStickerTransaction', 'getUserBoostTransaction', 'getUserSubscriptionTransaction', 'getUserFinancialTransaction')
            ->select(
                '_id',
                '_uid',
                'created_at',
                'credits',
                'financial_transactions__id',
                'credit_type'
            )
            ->where('credit_wallet_transactions.users__id', getUserID())
            ->customTableOptions($dataTableConfig);
    }

    /**
     * fetch user transaction list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchUserTransactionListData($userId)
    {
        $dataTableConfig = [
            'searchable' => [],
        ];

        return FinancialTransaction::leftJoin('credit_wallet_transactions', 'financial_transactions._id', '=', 'credit_wallet_transactions.financial_transactions__id')
            ->where('financial_transactions.users__id', $userId)
            ->select(
                __nestedKeyValues([
                    'financial_transactions' => [
                        '_id',
                        '_uid',
                        'created_at',
                        'updated_at',
                        'amount',
                        'users__id',
                        'currency_code',
                        'is_test',
                        'status',
                        'method',
                        '__data',
                    ],
                    'credit_wallet_transactions' => [
                        '_id as walletId',
                        'credit_type',
                    ],
                ])
            )
            ->dataTables($dataTableConfig)
            ->toArray();
    }

    /**
     * Store new coupon using provided data.
     *
     * @param  array  $inputData
     * @return mixed
     *---------------------------------------------------------------- */
    public function storeTransaction($inputData, $packageData, $userId = null)
    {
        $keyValues = [
            'status',
            'amount',
            'users__id',
            'method',
            'currency_code',
            'is_test',
            'txn_id',
            '__data',
        ];
        $financialTransaction = new FinancialTransaction;
        if($userId == null){
            $userId = getUserID();
        }
        // Check if new User added
        if ($financialTransaction->assignInputsAndSave($inputData, $keyValues)) {
            //wallet transaction store data
            $keyValues = [
                'status' => 1,
                'users__id' => $inputData['users__id'],
                'credits' => (int) $packageData['credits'],
                'financial_transactions__id' => $financialTransaction->_id,
                'credit_type' => 2, //Purchased
            ];
            $CreditWalletTransaction = new CreditWalletTransaction;
            // Check if new User added
            if ($CreditWalletTransaction->assignInputsAndSave($inputData, $keyValues)) {
                return true;
            }
        }

        return false;   // on failed
    }

    /**
     * Store new coupon using provided data.
     *
     * @param  array  $inputData
     * @return mixed
     *---------------------------------------------------------------- */
    public function storeWalletTransaction($inputData)
    {
        $keyValues = [
            'status',
            'users__id',
            'credits' => $inputData['credits'],
            'financial_transactions__id',
            'credit_type',
            'description'
        ];

        $CreditWalletTransaction = new CreditWalletTransaction;

        // Check if new User added
        if ($CreditWalletTransaction->assignInputsAndSave($inputData, $keyValues)) {
            return $CreditWalletTransaction;
        }

        return false;   // on failed
    }

    /**
     * Check if the transaction has already been processed
     *
     * @param [type] $id
     * @return boolean
     */
    public function isAlreadyProcessed($id)
    {
        return FinancialTransaction::where('txn_id', $id)->count();
    }

    /**
     * Store new coupon using provided data.
     *
     * @param  array  $inputData
     * @return mixed
     *---------------------------------------------------------------- */
    public function storeCredits($inputData)
    {
        $keyValues = [
            'status' => 1,
            'users__id' => $inputData['userId'],
            'credits' => (int) $inputData['credits'],
            'financial_transactions__id' => $inputData['txnId'],
            'credit_type' => 2, //Purchased
        ];

        $CreditWalletTransaction = new CreditWalletTransaction;
        // Check if new User added
        if ($CreditWalletTransaction->assignInputsAndSave([], $keyValues)) {
            return true;
        }
    }
}
