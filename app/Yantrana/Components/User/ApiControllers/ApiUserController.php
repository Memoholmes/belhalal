<?php
/**
* UserController.php - Controller file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\ApiControllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Support\CommonPostRequest;
use App\Yantrana\Components\User\UserEngine;
use App\Yantrana\Support\CommonUnsecuredPostRequest;
use App\Yantrana\Components\User\Requests\UserLoginRequest;
use App\Yantrana\Components\User\Requests\VerifyOtpRequest;
use App\Yantrana\Components\User\Requests\ReportUserRequest;
use App\Yantrana\Components\User\Requests\UserSignUpRequest;
use App\Yantrana\Components\User\Requests\UserContactRequest;
use App\Yantrana\Components\User\Requests\SendUserGiftRequest;
use App\Yantrana\Components\User\Requests\UserChangeEmailRequest;
use App\Yantrana\Components\User\Requests\UserResetPasswordRequest;
use App\Yantrana\Components\User\Requests\UserUpdatePasswordRequest;
use App\Yantrana\Components\User\Requests\ApiUserResetPasswordRequest;

class ApiUserController extends BaseController
{
    /**
     * @var UserEngine - User Engine
     */
    protected $userEngine;

    /**
     * Constructor.
     *
     * @param  UserEngine  $userEngine - User Engine
     *-----------------------------------------------------------------------*/
    public function __construct(UserEngine $userEngine)
    {
        $this->userEngine = $userEngine;
    }

    /**
     * Authenticate user based on post form data.
     *
     * @param object UserLoginRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function loginProcess(UserLoginRequest $request)
    {
        $processReaction = $this->userEngine->processLogin($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Authenticate user info
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function fetchUserAuthInfo()
    {
        $processReaction = $this->userEngine->authInfo();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get User my Disliked view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getWhoLikedMeData()
    {
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeMeData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get mutual like view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMutualLikeData()
    {
        //get mutual like data
        $processReaction = $this->userEngine->prepareMutualLikeData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get User my like view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMyLikeData()
    {
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeDislikedData(1);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get User my Disliked view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMyDislikedData()
    {
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeDislikedData(0);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get profile visitors view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getProfileVisitorData()
    {
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareProfileVisitorsData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get block user view and user list.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function blockUserList()
    {
        //get profile visitors data
        $processReaction = $this->userEngine->prepareBlockUserData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle report user request.
     *
     * @param object blockUser $userUid
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function processUnblockUser($userUid)
    {
        $processReaction = $this->userEngine->processUnblockUser($userUid);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function prepareSignUp()
    {
        $processReaction = $this->userEngine->prepareSignupData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processSignUp(UserSignUpRequest $request)
    {
        $processReaction = $this->userEngine->userSignUpProcess($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function resendActivationMail(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userEngine->resendActivationMail($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function verifyOtp(VerifyOtpRequest $request, $type)
    {
        $processReaction = $this->userEngine->verifyOtpProcess($request->all(), $type);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function requestNewPassword(CommonPostRequest $request)
    {
        $processReaction = $this->userEngine->requestNewPassword($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user signup
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function resetPassword(ApiUserResetPasswordRequest $request)
    {
        $processReaction = $this->userEngine->verifyOtpProcess($request->all(), 2);
        if($processReaction['reaction_code'] === 1) {
            $processReaction = $this->userEngine->resetPasswordForApp($request->all());
        }

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle reset password request.
     *
     * @param object UserResetPasswordRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function forgotPasswordResendOtp($userEmail)
    {
        $processReaction = $this->userEngine
            ->processForgotPasswordResendOtp($userEmail);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * prepare user profile
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readProfile($username)
    {
        $processReaction = $this->userEngine->prepareProfileDetails($username);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * process change email
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function changeEmail(UserChangeEmailRequest $request)
    {
        // $processReaction = $this->userEngine->changeEmailProcess($request->all());
        $processReaction = $this->userEngine->processChangeEmail($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }
    
    /**
     * Handle change password request.
     *
     * @param object UserUpdatePasswordRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function processChangePassword(UserUpdatePasswordRequest $request)
    {
        $processReaction = $this->userEngine
            ->processUpdatePassword(
                $request->only(
                    'new_password',
                    'current_password'
                )
            );

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Prepare user profile edit options
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function prepareProfileUpdate()
    {
        $processReaction = $this->userEngine->prepareProfileUpdate();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Process logout
     *
     * @return json object
     *-----------------------------------------------------------------------*/
    public function logout()
    {
        $processReaction = $this->userEngine->processAppLogout();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * get booster price and period
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getBoosterInfo()
    {
        $processReaction = $this->userEngine->getBoosterInfo();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processBoostProfile()
    {
        $processReaction = $this->userEngine->processBoostProfile();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle report user request.
     *
     * @param object blockUser $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function blockUser(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userEngine->processBlockUser($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle report user request.
     *
     * @param object ReportUserRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function reportUser(ReportUserRequest $request, $reportUserUid)
    {
        $processReaction = $this->userEngine->processReportUser($request->all(), $reportUserUid);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle send user gift request.
     *
     * @param object SendUserGiftRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function userSendGift(SendUserGiftRequest $request, $sendUserUId)
    {
        $processReaction = $this->userEngine->processUserSendGift($request->all(), $sendUserUId);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle user like dislike request.
     *
     * @param object UserResetPasswordRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function userLikeDislike($toUserUid, $like)
    {
        $processReaction = $this->userEngine->processUserLikeDislike($toUserUid, $like);

        //check reaction code equal to 1
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * prepare featured users.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getFeaturedUsers()
    {
        $processReaction = $this->userEngine->prepareFeaturedUsers();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle process contact request.
     *
     * @param object UserContactRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function contactProcess(UserContactRequest $request)
    {
        $processReaction = $this->userEngine->processContact($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function readWizardProfileData()
    {
        $processReaction = $this->userEngine->checkProfileStatus();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle process contact request.
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function deleteAccount(CommonPostRequest $request)
    {
        $processReaction = $this->userEngine->processDeleteAccount($request->all());
        return $this->processResponse($processReaction, [], [], true);
    }
}
