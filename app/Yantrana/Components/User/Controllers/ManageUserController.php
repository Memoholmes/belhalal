<?php

/**
 * ManageUserController.php - Controller file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\ManageUserEngine;
use App\Yantrana\Components\User\Requests\UserAddRequest;
use App\Yantrana\Components\User\Requests\GenerateFakeUsers;
use App\Yantrana\Components\User\Requests\UserUpdateRequest;
use App\Yantrana\Components\User\Requests\PaypalTransactionRequest;

class ManageUserController extends BaseController
{
    /**
     * @var  ManageUserEngine - ManageUser Engine
     */
    protected $manageUserEngine;

    /**
     * Constructor
     *
     * @param  ManageUserEngine  $manageUserEngine - ManageUser Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(ManageUserEngine $manageUserEngine)
    {
        $this->manageUserEngine = $manageUserEngine;
    }

    /**
     * Manage User List.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function userList($status, $userType = null)
    {
        return $this->loadManageView('user.manage.list', [
            'userType' => $userType
        ]);
    }

    /**
     * Manage User List.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function userDataTableList($status, $userType = null)
    {
        return $this->manageUserEngine->prepareUsersDataTableList($status, $userType);
    }

    /**
     * Load User Photos view.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function userPhotosView()
    {
        return $this->loadManageView('user.photos.list');
    }

    /**
     * Manage User Photos List.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function userPhotosList()
    {
        return $this->manageUserEngine->userPhotosDataTableList();
    }

    /**
     * Add new user view.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function addNewUserView()
    {
        return $this->loadManageView('user.manage.add');
    }

    /**
     * Add new user view.
     *
     * @param object UserAddRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function processAddNewUser(UserAddRequest $request)
    {
        $processReaction = $this->manageUserEngine->processAddUser($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true),
            $this->redirectTo('manage.user.view_list', ['status' => 1])
        );
    }

    /**
     * Edit User.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function editUser($userUid)
    {
        $processReaction = $this->manageUserEngine->prepareUserEditData($userUid);

        return $this->loadManageView('user.manage.edit', $processReaction['data']);
    }

    /**
     * Edit User.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUpdateUser($userUid, UserUpdateRequest $request)
    {
        $processReaction = $this->manageUserEngine->processUserUpdate($userUid, $request->all());

        if ($processReaction['reaction_code'] == 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('manage.user.view_list', ['status' => 1])
            );
        }

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Process Soft delete user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserSoftDelete($userUid)
    {
        $processReaction = $this->manageUserEngine->processSoftDeleteUser($userUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process delete photo of user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserPhotoDelete($userUid, $type, $profileOrPhotoUid)
    {
        $processReaction = $this->manageUserEngine->processUserPhotoDelete($userUid, $type, $profileOrPhotoUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process Permanent delete user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserPermanentDelete($userUid)
    {
        $processReaction = $this->manageUserEngine->processPermanentDeleteUser($userUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process Restore user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processRestoreUser($userUid)
    {
        $processReaction = $this->manageUserEngine->processUserRestore($userUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process Block user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserBlock($userUid)
    {
        $processReaction = $this->manageUserEngine->processBlockUser($userUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Process Unblock user.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserUnblock($userUid)
    {
        $processReaction = $this->manageUserEngine->processUnblockUser($userUid);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Get User Details.
     *
     * @param  string  $userUid
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserDetails($userUid)
    {
        $processReaction = $this->manageUserEngine->prepareUserDetails($userUid);

        return $this->loadManageView('user.manage.details', $processReaction['data']);
    }

    /**
     * fetch Fake User Generator Options
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function fetchFakeUserOptions()
    {
        $processReaction = $this->manageUserEngine->prepareFakeUserOptions();

        return $this->loadManageView('fake-data-generator.fake-users', $processReaction['data']);
    }

    /**
     * Generate fake users.
     *
     * @param object GenerateFakeUsers $request
     * @return json object
     *---------------------------------------------------------------- */
    public function generateFakeUser(GenerateFakeUsers $request)
    {
        $processReaction = $this->manageUserEngine->processGenerateFakeUser($request->all());

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Process Verify user profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processVerifyUserProfile($userUid)
    {
        $processReaction = $this->manageUserEngine->processVerifyUserProfile($userUid);

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Show user Transaction List.
     *
     *-----------------------------------------------------------------------*/
    public function manageUserTransactionList($userUid)
    {
        return $this->manageUserEngine->getUserTransactionList($userUid);
    }

    /**
     * Add User Allocate Credits
     *
     * @param   object PaypalTransactionRequest  $request
     * @return  json object
     */
    public function addUserAllocateCredits(PaypalTransactionRequest $request)
    {
        $request->validate(['allocate_credits' => 'required']);
        return $this->manageUserEngine->allocatedCreditsForUser($request->all());
    }

    /**
     * Process To Login Admin as a User
     *
     * @param   [type]  $userId  [$userId description]
     *
     * @return  [type]           [return description]
     */
    public function processToLoginAdminAsFakeUser($userId)
    {
        if (Auth::loginUsingId($userId)) {
            return redirect()->route('user.login');
        }
    }
}
