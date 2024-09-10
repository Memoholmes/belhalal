<?php
/**
* UserSettingController.php - Controller file
*
* This file is part of the UserSetting component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\UserSetting\Requests\UserBasicSettingAddRequest;
use App\Yantrana\Components\UserSetting\Requests\UserProfileSettingAddRequest;
use App\Yantrana\Components\UserSetting\Requests\UserProfileWizardRequest;
use App\Yantrana\Components\UserSetting\Requests\UserSettingRequest;
use App\Yantrana\Components\UserSetting\UserSettingEngine;
use App\Yantrana\Support\CommonUnsecuredPostRequest;

class UserSettingController extends BaseController
{
    /**
     * @var  UserSettingEngine - UserSetting Engine
     */
    protected $userSettingEngine;

    /**
     * Constructor
     *
     * @param  UserSettingEngine  $userSettingEngine - UserSetting Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(UserSettingEngine $userSettingEngine)
    {
        $this->userSettingEngine = $userSettingEngine;
    }

    /**
     * Show user setting view.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserSettingView($pageType)
    {
        $processReaction = $this->userSettingEngine->prepareUserSettings($pageType);

        abort_if($processReaction['reaction_code'] == 18, 404, $processReaction['message']);

        return $this->loadPublicView('user.settings.settings', $processReaction['data']);
    }

    /**
     * Get UserSetting Data.
     *
     * @param  string  $pageType
     * @return json object
     *---------------------------------------------------------------- */
    public function processStoreUserSetting(UserSettingRequest $request, $pageType)
    {
        $processReaction = $this->userSettingEngine
            ->processUserSettingStore($pageType, $request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process store user basic settings.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserBasicSetting(UserBasicSettingAddRequest $request)
    {
        $processReaction = $this->userSettingEngine->processStoreUserBasicSettings($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process profile Update Wizard.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function profileUpdateWizard(UserProfileWizardRequest $request)
    {
        $processReaction = $this->userSettingEngine->processStoreProfileWizard($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process store user basic settings.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processLocationData(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->processStoreLocationData($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process upload profile image.
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadProfileImage(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->processUploadProfileImage($request->all(), 'profile');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Process upload cover image.
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadCoverImage(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->processUploadCoverImage($request->all(), 'cover_image');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Process user profile settings
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserProfileSetting(UserProfileSettingAddRequest $request)
    {
        $processReaction = $this->userSettingEngine->processStoreUserProfileSetting($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Show user photos view.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserPhotosSetting()
    {
        $processReaction = $this->userSettingEngine->prepareUserPhotosSettings();

        return $this->loadPublicView('user.settings.photos', $processReaction['data']);
    }

    /**
     * Upload multiple photos
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadPhotos(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->processUploadPhotos($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload multiple photos
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function deleteUserPhotos($photoUid)
    {
        $processReaction = $this->userSettingEngine->processDeleteUserPhotos($photoUid);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Search Cities
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function searchStaticCities(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->searchStaticCities($request->get('search_query'));

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process store user city
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processStoreCity(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userSettingEngine->processStoreCity($request->get('selected_city_id'));

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }
}
