<?php
/**
* FinancialTransactionController.php - Controller file
*
* This file is part of the FinancialTransaction User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\FinancialTransaction\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\FinancialTransaction\FinancialTransactionEngine;

class FinancialTransactionController extends BaseController
{
    /**
     * @var  FinancialTransactionEngine - FinancialTransaction Engine
     */
    protected $financialTransactionEngine;

    /**
     * Constructor
     *
     * @param  FinancialTransactionEngine  $financialTransactionEngine - FinancialTransaction Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(FinancialTransactionEngine $financialTransactionEngine)
    {
        $this->financialTransactionEngine = $financialTransactionEngine;
    }

    /**
     * Manage Financial Transaction load view.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function financialTransactionViewList($transactionType)
    {
        return $this->loadManageView('financial-transaction.manage.list');
    }

    /**
     * Get Transaction List data.
     *
     *-----------------------------------------------------------------------*/
    public function getTransactionList($transactionType)
    {
        return $this->financialTransactionEngine->prepareTransactionList($transactionType);
    }

    /**
     * Delete all test transaction
     *
     *
     * @return  void
     *-----------------------------------------------------------------------*/
    public function deleteAllTestTransaction()
    {
        $processReaction = $this->financialTransactionEngine->processDeleteAllTestTransaction();

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }
}
