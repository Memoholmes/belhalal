<?php
/**
* FinancialTransactionRepository.php - Repository file
*
* This file is part of the FinancialTransaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FinancialTransaction\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\FinancialTransaction\Models\FinancialTransaction;
use DB;

class FinancialTransactionRepository extends BaseRepository
{
    /**
     * primary model instance
     * eg. YourModelModel::class;
     *
     * @var object
     */
    protected $primaryModel = FinancialTransaction::class;
    /**
     * Constructor.
     *
     * @param  Page  $gift - gift Model
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
    }

    /**
     * fetch gift data.
     *
     * @param  int  $idOrUid
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetch($idOrUid)
    {
        //check is numeric
        if (is_numeric($idOrUid)) {
            return FinancialTransaction::where('_id', $idOrUid)->first();
        } else {
            return FinancialTransaction::where('_uid', $idOrUid)->first();
        }
    }

    /**
     * fetch all transaction list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchTransactionListData($transactionType)
    {
        $dataTableConfig = [
            'searchable' => [
                'userFullName' => DB::raw('CONCAT(users.first_name, " ", users.last_name)'),
                'created_at' => 'financial_transactions.created_at',
                'amount' => 'financial_transactions.amount',
            ],
        ];

        return FinancialTransaction::leftJoin('users', 'financial_transactions.users__id', '=', 'users._id')
            ->select(
                __nestedKeyValues([
                    'financial_transactions.*',
                    'users' => [
                        '_id as userId',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS userFullName'),
                    ],
                ])
            )
            ->where('financial_transactions.is_test', $transactionType)
            ->dataTables($dataTableConfig)
            ->toArray();
    }

    /**
     * fetch all test transactions.
     *
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchAllTestTransactions()
    {
        return FinancialTransaction::where('is_test', 1)
            ->get();
    }

    /**
     * Delete all test transactions.
     *
     * @param  object  $transaction
     * @return bool
     *---------------------------------------------------------------- */
    public function deleteAllTransaction($transactionsIds)
    {
        $financialTransaction = new FinancialTransaction;
        // Check if page deleted
        if ($financialTransaction->whereIn('_id', $transactionsIds)->delete()) {
            activityLog('Delete all test transactions');

            return  true;
        }

        return false;
    }
}
