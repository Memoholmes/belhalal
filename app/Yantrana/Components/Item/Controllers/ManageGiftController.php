<?php
/**
* ManageGiftController.php - Controller file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Item\ManageGiftEngine;
use App\Yantrana\Components\Item\Requests\GiftAddRequest;
use App\Yantrana\Components\Item\Requests\GiftEditRequest;

class ManageGiftController extends BaseController
{
    /**
     * @var ManageGiftEngine - ManageGift Engine
     */
    protected $manageGiftEngine;

    /**
     * Constructor.
     *
     * @param  ManageGiftEngine  $ManageGiftEngine - ManageGift Engine
     *-----------------------------------------------------------------------*/
    public function __construct(ManageGiftEngine $manageGiftEngine)
    {
        $this->manageGiftEngine = $manageGiftEngine;
    }

    /**
     * Show Gift List View.
     *
     *-----------------------------------------------------------------------*/
    public function giftListView()
    {
        $processReaction = $this->manageGiftEngine->prepareGiftList();

        return $this->loadManageView('items.gift.manage.list', $processReaction['data']);
    }

    /**
     * Show Gift Add View.
     *
     *-----------------------------------------------------------------------*/
    public function giftAddView()
    {
        return $this->loadManageView('items.gift.manage.add');
    }

    /**
     * Handle add new page request.
     *
     * @param  GiftAddRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function addGift(GiftAddRequest $request)
    {
        $processReaction = $this->manageGiftEngine
            ->processAddNewGift($request->all());

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.item.gift.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Show Gift Edit View.
     *
     *-----------------------------------------------------------------------*/
    public function giftEditView($giftUId)
    {
        $processReaction = $this->manageGiftEngine->prepareGiftUpdateData($giftUId);

        return $this->loadManageView('items.gift.manage.edit', $processReaction['data']);
    }

    /**
     * Handle edit new gift request.
     *
     * @param  GiftEditRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function editGift(GiftEditRequest $request, $giftUId)
    {
        $processReaction = $this->manageGiftEngine
            ->processEditGift($request->all(), $giftUId);

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.item.gift.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Handle delete gift data request.
     *
     * @param  int  $giftUId
     * @return json object
     *---------------------------------------------------------------- */
    public function deleteGift($giftUId)
    {
        $processReaction = $this->manageGiftEngine->processDeleteGift($giftUId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true),
            $this->redirectTo('manage.item.gift.view')
        );
    }
}
