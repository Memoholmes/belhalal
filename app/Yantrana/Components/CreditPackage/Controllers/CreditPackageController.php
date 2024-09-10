<?php
/**
* CreditPackageController.php - Controller file
*
* This file is part of the CreditPackage component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\CreditPackage\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\CreditPackage\CreditPackageEngine;
use App\Yantrana\Components\CreditPackage\Requests\CreditPackageAddRequest;
use App\Yantrana\Components\CreditPackage\Requests\CreditPackageEditRequest;

class CreditPackageController extends BaseController
{
    /**
     * @var  CreditPackageEngine - CreditPackage Engine
     */
    protected $creditPackageEngine;

    /**
     * Constructor
     *
     * @param  CreditPackageEngine  $creditPackageEngine - CreditPackage Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(CreditPackageEngine $creditPackageEngine)
    {
        $this->creditPackageEngine = $creditPackageEngine;
    }

    /**
     * Show Credit Package List View.
     *
     *-----------------------------------------------------------------------*/
    public function getCreditPackageList()
    {
        $processReaction = $this->creditPackageEngine->prepareCreditPackageList();

        return $this->loadManageView('credit-package.manage.list', $processReaction['data']);
    }

    /**
     * Show Package Add View.
     *
     *-----------------------------------------------------------------------*/
    public function packageAddView()
    {
        return $this->loadManageView('credit-package.manage.add');
    }

    /**
     * Handle add new package request.
     *
     * @param  CreditPackageAddRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function addPackage(CreditPackageAddRequest $request)
    {
        $processReaction = $this->creditPackageEngine
            ->processAddNewPackage($request->all());

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.credit_package.read.list')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Show Package Edit View.
     *
     *-----------------------------------------------------------------------*/
    public function packageEditView($packageUId)
    {
        $processReaction = $this->creditPackageEngine->preparePackageUpdateData($packageUId);

        return $this->loadManageView('credit-package.manage.edit', $processReaction['data']);
    }

    /**
     * Handle edit new package request.
     *
     * @param  CreditPackageEditRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function editPackage(CreditPackageEditRequest $request, $packageUId)
    {
        $processReaction = $this->creditPackageEngine
            ->processEditPackage($request->all(), $packageUId);

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.credit_package.read.list')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Handle delete package data request.
     *
     * @param  int  $packageUId
     * @return json object
     *---------------------------------------------------------------- */
    public function processDeletePackage($packageUId)
    {
        $processReaction = $this->creditPackageEngine->processDeletePackage($packageUId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true),
            $this->redirectTo('manage.credit_package.read.list')
        );
    }
}
