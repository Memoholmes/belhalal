<?php
/**
* ManageStickerEngine.php - Main component file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Item\Repositories\ManageItemRepository;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use Auth;

class ManageStickerEngine extends BaseEngine
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
     * @param  MediaEngine  $mediaEngine - Media Engine
     * @param  ManageItemRepository  $manageItemRepository - ManageItem Repository
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
     * get sticker list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareStickerList()
    {
        $stickerCollection = $this->manageItemRepository->fetchListData(2);

        $stickerListData = [];
        if (! __isEmpty($stickerCollection)) {
            foreach ($stickerCollection as $key => $stickerData) {
                $stickerImageUrl = '';
                if (isset($stickerData->file_name) and ! __isEmpty($stickerData->file_name)) {
                    $stickerImageFolderPath = getPathByKey('sticker_image', ['{_uid}' => $stickerData->_uid]);
                    $stickerImageUrl = getMediaUrl($stickerImageFolderPath, $stickerData->file_name);
                }

                $stickerListData[] = [
                    '_id' => $stickerData['_id'],
                    '_uid' => $stickerData['_uid'],
                    'title' => $stickerData['title'],
                    'created_at' => formatDate($stickerData['created_at']),
                    'updated_at' => formatDate($stickerData['updated_at']),
                    'status' => configItem('status_codes', $stickerData['status']),
                    'normal_price' => intval($stickerData['normal_price']),
                    'premium_price' => $stickerData['premium_price'],
                    'stickerImageUrl' => $stickerImageUrl,
                    'is_premium_sticker' => ($stickerData['premium_only'])
                        ? __tr('Yes') : __tr('No'),
                ];
            }
        }

        return $this->engineReaction(1, [
            'stickerListData' => $stickerListData,
        ]);
    }

    /**
     * Process upload sticker image.
     *
     * @param  array  $inputData
     * @return engineReaction array
     *---------------------------------------------------------------- */
    public function processUploadStickerImage($inputData, $requestFor)
    {
        // Fetch Authority of login user
        $userAuthority = $this->userRepository->fetchUserAuthority(getUserID());

        //check if empty
        if (__isEmpty($userAuthority)) {
            return $this->engineReaction(1, null, __tr('User not exist.'));
        }

        $file = $inputData['filepond'];
        $fileOriginalName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $fileBaseName = str_slug(basename($fileOriginalName, '.'.$fileExtension));
        $fileName = $fileBaseName.".$fileExtension";

        $storeStickerData = [
            'type' => 2, // Sticker
            'status' => 2, // Inactive
            'user_authorities__id' => $userAuthority->_id,
            'file_name' => $fileName,
        ];

        //Check if sticker added
        if ($newSticker = $this->manageItemRepository->storeItem($storeStickerData)) {
            $stickerImageFolderPath = getPathByKey('sticker_image', ['{_uid}' => $newSticker->_uid]);
            $uploadedFile = $this->mediaEngine->processUpload(
                $inputData,
                $stickerImageFolderPath,
                $requestFor
            );
            if ($uploadedFile['reaction_code'] == 1) {
                return $this->engineReaction(1, [
                    '_uid' => $newSticker->_uid,
                    'redirect_to' => route('manage.item.sticker.edit.view', ['stickerUId' => $newSticker->_uid]),
                ], __tr('Sticker uploaded successfully'));
            }

            return $uploadedFile;
        }

        return $this->engineReaction(2, null, __tr('Something went wrong on server.'));
    }

    /**
     * Process add new sticker.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function prepareForAddNewSticker($inputData)
    {
        //get user id
        $userId = Auth::id();

        // Fetch Authority of login user
        $userAuthority = $this->userRepository->fetchUserAuthority($userId);

        //check if empty
        if (__isEmpty($userAuthority)) {
            return $this->engineReaction(1, null, __tr('User not exist.'));
        }

        // Check if normal price exists
        if (__ifIsset($inputData['normal_price'])) {
            if ($inputData['normal_price'] < $inputData['premium_price']) {
                return $this->engineReaction(2, ['show_message' => true], __tr('Normal price may not be greater than premium price.'));
            }
        }

        //store data
        $storeData = [
            'title' => $inputData['title'],
            'type' => 2, //sticker type
            'file_name' => $inputData['sticker_image'],
            'normal_price' => (__ifIsset($inputData['normal_price']))
                ? $inputData['normal_price'] : 0,
            'premium_price' => $inputData['premium_price'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
            'user_authorities__id' => $userAuthority->_id,
            'premium_only' => (__ifIsset($inputData['is_for_premium_user'])
                and $inputData['is_for_premium_user'] == 'on')
                ? 1 : null,
        ];

        //Check if sticker added
        if ($newSticker = $this->manageItemRepository->storeItem($storeData)) {
            $stickerImageFolderPath = getPathByKey('sticker_image', ['{_uid}' => $newSticker->_uid]);
            $uploadedMedia = $this->mediaEngine->processMoveFile($stickerImageFolderPath, $inputData['sticker_image']);
            // check if file uploaded successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                return $this->engineReaction(1, [], __tr('Sticker added successfully.'));
            }
            //error message
            return $this->engineReaction(2, null, __tr('Something went wrong on server.'));
        }

        return $this->engineReaction(2, null, __tr('Sticker not added.'));
    }

    /**
     * get gift edit data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareStickerUpdateData($stickerUId)
    {
        $stickerCollection = $this->manageItemRepository->fetch($stickerUId);

        //if is empty then show error message
        if (__isEmpty($stickerCollection)) {
            return $this->engineReaction(1, null, __tr('Sticker does not exist'));
        }

        $stickerImageUrl = '';
        $stickerImageFolderPath = getPathByKey('sticker_image', ['{_uid}' => $stickerCollection->_uid]);
        $stickerImageUrl = getMediaUrl($stickerImageFolderPath, $stickerCollection->file_name);

        $stickerEditData = [];
        if (! __isEmpty($stickerCollection)) {
            $stickerEditData = [
                '_id' => $stickerCollection['_id'],
                '_uid' => $stickerCollection['_uid'],
                'title' => $stickerCollection['title'],
                'file_name' => $stickerCollection['file_name'],
                'normal_price' => isset($stickerCollection['normal_price']) ? $stickerCollection['normal_price'] : 0,
                'premium_price' => $stickerCollection['premium_price'],
                'status' => $stickerCollection['status'],
                'sticker_image_url' => $stickerImageUrl,
                'premium_only' => $stickerCollection['premium_only'],
            ];
        }

        return $this->engineReaction(1, [
            'stickerEditData' => $stickerEditData,
        ]);
    }

    /**
     * Process eit new sticker.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processEditSticker($inputData, $stickerUId)
    {
        $stickerCollection = $this->manageItemRepository->fetch($stickerUId);

        //if is empty then show error message
        if (__isEmpty($stickerCollection)) {
            return $this->engineReaction(1, null, __tr('Sticker does not exist'));
        }
        $isStickerUpdate = false;
        //update data
        $updateData = [
            'title' => $inputData['title'],
            'normal_price' => (__ifIsset($inputData['normal_price']))
                ? $inputData['normal_price'] : 0,
            'premium_price' => $inputData['premium_price'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
            'premium_only' => (__ifIsset($inputData['is_for_premium_user'])
                and $inputData['is_for_premium_user'] == 'on')
                ? 1 : null,
        ];

        // check if update image exists
        if (! \__isEmpty($inputData['sticker_image'])) {
            $stickerImageFolderPath = getPathByKey('sticker_image', ['{_uid}' => $stickerCollection->_uid]);
            $this->mediaEngine->delete($stickerImageFolderPath, $stickerCollection->file_name);
            $uploadedMedia = $this->mediaEngine->processMoveFile($stickerImageFolderPath, $inputData['sticker_image']);
            // check if file update successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                $isStickerUpdate = true;
                $updateData['file_name'] = $inputData['sticker_image'];
            }
        }

        //Check if sticker added
        if ($this->manageItemRepository->updateItem($stickerCollection, $updateData)) {
            $isStickerUpdate = true;
        }

        // Check if sticker updated
        if ($isStickerUpdate) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Sticker updated successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Sticker not updated.'));
    }

    /**
     * Process sticker delete.
     *
     * @param int pageUId
     * @return array
     *---------------------------------------------------------------- */
    public function processDeleteSticker($stickerUId)
    {
        $stickerCollection = $this->manageItemRepository->fetch($stickerUId);

        //if is empty then show error message
        if (__isEmpty($stickerCollection)) {
            return $this->engineReaction(1, null, __tr('Sticker does not exist'));
        }

        //Check if gift deleted
        if ($this->manageItemRepository->delete($stickerCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Sticker deleted successfully.'));
        }

        return $this->engineReaction(18, null, __tr('Sticker not deleted.'));
    }
}
