<?php
/**
* ManageGiftEngine.php - Main component file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Item\Repositories\ManageItemRepository;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;

class ManageGiftEngine extends BaseEngine
{
    /**
     * @var ManageItemRepository - ManageItem Repository
     */
    protected $manageItemRepository;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var  MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * Constructor.
     *
     * @param  ManageItemRepository  $manageItemRepository - ManageItem Repository
     * @param  MediaEngine  $mediaEngine - Media Engine
     *
     *-----------------------------------------------------------------------*/
    public function __construct(
        ManageItemRepository $manageItemRepository,
        UserRepository $userRepository,
        MediaEngine $mediaEngine
    ) {
        $this->manageItemRepository = $manageItemRepository;
        $this->userRepository = $userRepository;
        $this->mediaEngine = $mediaEngine;
    }

    /**
     * get gift list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareGiftList()
    {
        $giftCollection = $this->manageItemRepository->fetchListData(1);

        $giftListData = [];
        if (! __isEmpty($giftCollection)) {
            foreach ($giftCollection as $key => $giftData) {
                $giftImageUrl = '';
                if (isset($giftData->file_name) and ! __isEmpty($giftData->file_name)) {
                    $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $giftData->_uid]);
                    $giftImageUrl = getMediaUrl($giftImageFolderPath, $giftData->file_name);
                }

                $giftListData[] = [
                    '_id' => $giftData['_id'],
                    '_uid' => $giftData['_uid'],
                    'title' => $giftData['title'],
                    'created_at' => formatDate($giftData['created_at']),
                    'updated_at' => formatDate($giftData['updated_at']),
                    'status' => configItem('status_codes', $giftData['status']),
                    'normal_price' => intval($giftData['normal_price']),
                    'premium_price' => $giftData['premium_price'],
                    'giftImageUrl' => $giftImageUrl,
                ];
            }
        }

        return $this->engineReaction(1, [
            'giftListData' => $giftListData,
        ]);
    }

    /**
     * Process add new gift.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processAddNewGift($inputData)
    {
        //get user id
        $userId = getUserID();

        // Fetch Authority of login user
        $userAuthority = $this->userRepository->fetchUserAuthority($userId);

        //check if empty
        if (__isEmpty($userAuthority)) {
            return $this->engineReaction(1, null, __tr('User not exist.'));
        }

        $userDetails = getUserAuthInfo();

        //store data
        $storeData = [
            'title' => $inputData['title'],
            'type' => 1, //gift type
            'normal_price' => $inputData['normal_price'],
            'premium_price' => $inputData['premium_price'],
            'file_name' => $inputData['gift_image'],
            'status' => (isset($inputData['status'])
                and $inputData['status'] == 'on') ? 1 : 2,
            'user_authorities__id' => array_get($userDetails, 'profile.authority_id'),
        ];

        //Check if gift added
        if ($newGift = $this->manageItemRepository->storeItem($storeData)) {
            $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $newGift->_uid]);
            $uploadedMedia = $this->mediaEngine->processMoveFile($giftImageFolderPath, $inputData['gift_image']);

            // check if file uploaded successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                return $this->engineReaction(1, [], __tr('Gift added successfully.'));
            }

            return $this->engineReaction(2, null, __tr('Something went wrong on server.'));
        }

        return $this->engineReaction(2, null, __tr('Gift not added.'));
    }

    /**
     * get gift edit data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareGiftUpdateData($giftUId)
    {
        $giftCollection = $this->manageItemRepository->fetch($giftUId);

        //if is empty then show error message
        if (__isEmpty($giftCollection)) {
            return $this->engineReaction(1, null, __tr('Gift does not exist'));
        }

        $giftImageUrl = '';
        $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $giftCollection->_uid]);
        $giftImageUrl = getMediaUrl($giftImageFolderPath, $giftCollection->file_name);

        $giftEditData = [];
        if (! __isEmpty($giftCollection)) {
            $giftEditData = [
                '_id' => $giftCollection['_id'],
                '_uid' => $giftCollection['_uid'],
                'title' => $giftCollection['title'],
                'file_name' => $giftCollection['file_name'],
                'normal_price' => intval($giftCollection['normal_price']),
                'premium_price' => $giftCollection['premium_price'],
                'status' => $giftCollection['status'],
                'gift_image_url' => $giftImageUrl,
            ];
        }

        return $this->engineReaction(1, [
            'giftEditData' => $giftEditData,
        ]);
    }

    /**
     * Process edit gift.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processEditGift($inputData, $giftUId)
    {
        $giftDetails = $this->manageItemRepository->fetch($giftUId);

        //if is empty then show error message
        if (__isEmpty($giftDetails)) {
            return $this->engineReaction(1, null, __tr('Gift does not exist'));
        }

        $isGiftUpdate = false;

        //update data
        $updateData = [
            'title' => $inputData['title'],
            'normal_price' => $inputData['normal_price'],
            'premium_price' => $inputData['premium_price'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
        ];

        // check if update image exists
        if (! \__isEmpty($inputData['gift_image'])) {
            $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $giftDetails->_uid]);
            $this->mediaEngine->delete($giftImageFolderPath, $giftDetails->file_name);
            $uploadedMedia = $this->mediaEngine->processMoveFile($giftImageFolderPath, $inputData['gift_image']);
            // check if file update successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                $isGiftUpdate = true;
                $updateData['file_name'] = $inputData['gift_image'];
            }
        }

        // Check if gift updated
        if ($this->manageItemRepository->updateItem($giftDetails, $updateData)) {
            $isGiftUpdate = true;
            return $this->engineReaction(1, ['show_message' => true], __tr('Gift updated successfully.'));
        }

        // Check if gift updated
        if ($isGiftUpdate) {
            return $this->engineReaction(1, null, __tr('Gift updated successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Gift not updated.'));
    }

    /**
     * Process gift delete.
     *
     * @param int pageUId
     * @return array
     *---------------------------------------------------------------- */
    public function processDeleteGift($giftUId)
    {
        $giftCollection = $this->manageItemRepository->fetch($giftUId);

        //if is empty then show error message
        if (__isEmpty($giftCollection)) {
            return $this->engineReaction(1, null, __tr('Gift does not exist'));
        }

        //Check if gift deleted
        if ($this->manageItemRepository->delete($giftCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Gift deleted successfully.'));
        }

        return $this->engineReaction(18, null, __tr('Gift not deleted.'));
    }
}
