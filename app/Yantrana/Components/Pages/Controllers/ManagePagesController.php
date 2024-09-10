<?php
/**
* ManagePagesController.php - Controller file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Pages\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Pages\ManagePagesEngine;
use App\Yantrana\Components\Pages\Requests\ManagePagesAddRequest;
use App\Yantrana\Components\Pages\Requests\ManagePagesEditRequest;
use Illuminate\Http\Request;

class ManagePagesController extends BaseController
{
    /**
     * @var ManagePagesEngine - ManagePages Engine
     */
    protected $managePagesEngine;

    /**
     * Constructor.
     *
     * @param  ManagePagesEngine  $managePagesEngine - ManagePages Engine
     *-----------------------------------------------------------------------*/
    public function __construct(ManagePagesEngine $managePagesEngine)
    {
        $this->managePagesEngine = $managePagesEngine;
    }

    /**
     * Show Page List View.
     *
     *-----------------------------------------------------------------------*/
    public function pageListView()
    {
        return $this->loadManageView('pages.manage.list');
    }

    /**
     * Get Datatable data.
     *
     *-----------------------------------------------------------------------*/
    public function getDatatableData()
    {
        return $this->managePagesEngine->preparePageList();
    }

    /**
     * Show Page Add View.
     *
     *-----------------------------------------------------------------------*/
    public function pageAddView()
    {
        return $this->loadManageView('pages.manage.add');
    }

    /**
     * Handle add new page request.
     *
     * @param  ManagePagesAddRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function processAddPage(ManagePagesAddRequest $request)
    {
        $processReaction = $this->managePagesEngine
            ->prepareForAddNewPage($request->all());

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.page.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Show Page Edit View.
     *
     *-----------------------------------------------------------------------*/
    public function pageEditView($pageUId)
    {
        $processReaction = $this->managePagesEngine->prepareUpdateData($pageUId);

        return $this->loadManageView('pages.manage.edit', $processReaction['data'], [
            'compress_page' => false
        ]);
    }

    /**
     * Handle edit new page request.
     *
     * @param  ManagePagesEditRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function processEditPage(ManagePagesEditRequest $request, $pageUId)
    {
        $processReaction = $this->managePagesEngine
            ->prepareForEditNewPage($request->all(), $pageUId);

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.page.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Handle delete page data request.
     *
     * @param  int  $pageUId
     * @return json object
     *---------------------------------------------------------------- */
    public function delete($pageUId)
    {
        $processReaction = $this->managePagesEngine->processDelete($pageUId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }
}
