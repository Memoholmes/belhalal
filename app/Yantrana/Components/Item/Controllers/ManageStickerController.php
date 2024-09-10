<?php
/**
* ManageStickerController.php - Controller file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Item\ManageStickerEngine;
use App\Yantrana\Components\Item\Requests\StickerAddRequest;
use App\Yantrana\Components\Item\Requests\StickerEditRequest;

class ManageStickerController extends BaseController
{
    /**
     * @var ManageStickerEngine - ManageSticker Engine
     */
    protected $manageStickerEngine;

    /**
     * Constructor.
     *
     * @param  ManageStickerEngine  $manageStickerEngine - ManageSticker Engine
     *-----------------------------------------------------------------------*/
    public function __construct(ManageStickerEngine $manageStickerEngine)
    {
        $this->manageStickerEngine = $manageStickerEngine;
    }

    /**
     * Show Sticker List View.
     *
     *-----------------------------------------------------------------------*/
    public function stickerListView()
    {
        $processReaction = $this->manageStickerEngine->prepareStickerList();

        return $this->loadManageView('items.sticker.manage.list', $processReaction['data']);
    }

    /**
     * Show Sticker Add View.
     *
     *-----------------------------------------------------------------------*/
    public function stickerAddView()
    {
        return $this->loadManageView('items.sticker.manage.add');
    }

    /**
     * Handle upload sticker image request.
     *
     * @param  StickerAddRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function uploadStickerImage(ManageItemAddRequest $request)
    {
        $processReaction = $this->manageStickerEngine->processUploadStickerImage($request->all(), 'sticker');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle add new page request.
     *
     * @param  ManageItemAddRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function addSticker(StickerAddRequest $request)
    {
        $processReaction = $this->manageStickerEngine
            ->prepareForAddNewSticker($request->all());

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.item.sticker.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Show Sticker Edit View.
     *
     *-----------------------------------------------------------------------*/
    public function stickerEditView($giftUId)
    {
        $processReaction = $this->manageStickerEngine->prepareStickerUpdateData($giftUId);

        return $this->loadManageView('items.sticker.manage.edit', $processReaction['data']);
    }

    /**
     * Handle edit new sticker request.
     *
     * @param  StickerEditRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function editSticker(StickerEditRequest $request, $stickerUId)
    {
        $processReaction = $this->manageStickerEngine
            ->processEditSticker($request->all(), $stickerUId);

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.item.sticker.view')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Handle delete sticker data request.
     *
     * @param  int  $stickerUId
     * @return json object
     *---------------------------------------------------------------- */
    public function deleteSticker($stickerUId)
    {
        $processReaction = $this->manageStickerEngine->processDeleteSticker($stickerUId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true),
            $this->redirectTo('manage.item.sticker.view')
        );
    }
}
