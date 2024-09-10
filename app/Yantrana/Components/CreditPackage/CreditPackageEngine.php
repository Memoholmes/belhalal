<?php
/**
* CreditPackageEngine.php - Main component file
*
* This file is part of the CreditPackage component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\CreditPackage;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\CreditPackage\Interfaces\CreditPackageEngineInterface;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;

class CreditPackageEngine extends BaseEngine implements CreditPackageEngineInterface
{
    /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var  MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * Constructor
     *
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * @param  MediaEngine  $mediaEngine - Media Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(
        CreditPackageRepository $creditPackageRepository,
        UserRepository $userRepository,
        MediaEngine $mediaEngine
    ) {
        $this->creditPackageRepository = $creditPackageRepository;
        $this->userRepository = $userRepository;
        $this->mediaEngine = $mediaEngine;
    }

    /**
     * get credit package list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareCreditPackageList()
    {
        $creditPackageCollection = $this->creditPackageRepository->fetchAllCreditPackage();

        $creditPackageData = [];
        if (! __isEmpty($creditPackageCollection)) {
            foreach ($creditPackageCollection as $key => $package) {
                $packageImageUrl = '';
                if (isset($package->image) and ! __isEmpty($package->image)) {
                    $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $package->_uid]);
                    $packageImageUrl = getMediaUrl($packageImageFolderPath, $package->image);
                }
                $creditPackageData[] = [
                    '_id' => $package['_id'],
                    '_uid' => $package['_uid'],
                    'title' => $package['title'],
                    'created_at' => formatDate($package['created_at']),
                    'updated_at' => formatDate($package['updated_at']),
                    'status' => configItem('status_codes', $package['status']),
                    'price' => intval($package['price']),
                    'credits' => $package['credits'],
                    'packageImageUrl' => $packageImageUrl,
                ];
            }
        }
        //success response
        return $this->engineReaction(1, [
            'creditPackageData' => $creditPackageData,
        ]);
    }

    /**
     * Process add new package.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processAddNewPackage($inputData)
    {
        // Fetch Authority of login user
        $user = $this->userRepository->fetch(getUserID());

        //check if empty
        if (__isEmpty($user)) {
            return $this->engineReaction(1, null, __tr('User not exist.'));
        }

        //store data
        $storeData = [
            'title' => $inputData['title'],
            'price' => $inputData['price'],
            'credits' => $inputData['credits'],
            'image' => $inputData['package_image'],
            'status' => (isset($inputData['status'])
                and $inputData['status'] == 'on') ? 1 : 2,
            'users__id' => $user->_id,
        ];

        //Check if package added
        if ($newPackage = $this->creditPackageRepository->storePackage($storeData)) {
            $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $newPackage->_uid]);
            $uploadedMedia = $this->mediaEngine->processMoveFile($packageImageFolderPath, $inputData['package_image']);
            // check if file uploaded successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                return $this->engineReaction(1, [], __tr('Package added successfully.'));
            }

            return $this->engineReaction(2, null, __tr('Something went wrong on server.'));
        }
        //error response
        return $this->engineReaction(2, null, __tr('Package not added.'));
    }

    /**
     * get package edit data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function preparePackageUpdateData($packageUId)
    {
        $packageCollection = $this->creditPackageRepository->fetch($packageUId);

        //if is empty then show error message
        if (__isEmpty($packageCollection)) {
            return $this->engineReaction(1, null, __tr('Package does not exist'));
        }

        $packageImageUrl = '';
        $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $packageCollection->_uid]);
        $packageImageUrl = getMediaUrl($packageImageFolderPath, $packageCollection->image);

        $packageEditData = [];
        if (! __isEmpty($packageCollection)) {
            $packageEditData = [
                '_id' => $packageCollection['_id'],
                '_uid' => $packageCollection['_uid'],
                'title' => $packageCollection['title'],
                'image' => $packageCollection['image'],
                'price' => intval($packageCollection['price']),
                'credits' => $packageCollection['credits'],
                'status' => $packageCollection['status'],
                'packageImageUrl' => $packageImageUrl,
            ];
        }

        return $this->engineReaction(1, [
            'packageEditData' => $packageEditData,
        ]);
    }

    /**
     * Process edit package.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processEditPackage($inputData, $packageUId)
    {
        $packageCollection = $this->creditPackageRepository->fetch($packageUId);

        //if is empty then show error message
        if (__isEmpty($packageCollection)) {
            return $this->engineReaction(1, null, __tr('Package does not exist'));
        }

        $isPackageUpdate = false;
        //update data
        $updateData = [
            'title' => $inputData['title'],
            'price' => $inputData['price'],
            'credits' => $inputData['credits'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
        ];

        // check if update image exists
        if (! \__isEmpty($inputData['package_image'])) {
            $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $packageCollection->_uid]);
            $this->mediaEngine->delete($packageImageFolderPath, $packageCollection->image);
            $uploadedMedia = $this->mediaEngine->processMoveFile($packageImageFolderPath, $inputData['package_image']);
            // check if file update successfully
            if ($uploadedMedia['reaction_code'] == 1) {
                $isPackageUpdate = true;
                $updateData['image'] = $inputData['package_image'];
            }
        }

        // Check if package updated
        if ($this->creditPackageRepository->updatePackage($packageCollection, $updateData)) {
            $isPackageUpdate = true;
        }

        // Check if package updated
        if ($isPackageUpdate) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Package updated successfully.'));
        }
        //error response
        return $this->engineReaction(2, ['show_message' => true], __tr('Package not updated.'));
    }

    /**
     * Process package delete.
     *
     * @param int packageUId
     * @return array
     *---------------------------------------------------------------- */
    public function processDeletePackage($packageUId)
    {
        $packageCollection = $this->creditPackageRepository->fetch($packageUId);

        //if is empty then show error message
        if (__isEmpty($packageCollection)) {
            return $this->engineReaction(1, null, __tr('Package does not exist'));
        }

        //Check if package deleted
        if ($this->creditPackageRepository->delete($packageCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Package deleted successfully.'));
        }

        //error response
        return $this->engineReaction(18, null, __tr('Package not deleted.'));
    }
}
