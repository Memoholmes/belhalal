<?php

/**
 * UserController.php - Controller file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
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
use App\Yantrana\Components\User\Requests\UserForgotPasswordRequest;
use App\Yantrana\Components\User\Requests\UserUpdatePasswordRequest;

class UserController extends BaseController
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
     * Show login view.
     *---------------------------------------------------------------- */
    public function login()
    {
        return $this->loadView('user.login');
    }

    /**
     * Authenticate user based on post form data.
     *
     * @param object UserLoginRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function loginProcess(UserLoginRequest $request)
    {
        if(getStoreSettings('allow_recaptcha')) {
            $request->validate(['g-recaptcha-response' => 'required'], [
                'g-recaptcha-response' => __tr('The recaptcha field is required.')
            ]);
        }
        $processReaction = $this->userEngine->processLogin($request->all());

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('user.profile_view', ['username' => getUserAuthInfo('profile.username')])
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Perform user logout action.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function logout()
    {
        $processReaction = $this->userEngine->processLogout();

        return redirect()->route('user.login');
    }

    /**
     * Show Sign Up View.
     *
     *-----------------------------------------------------------------------*/
    public function signUp()
    {
        return $this->loadView('user.sign-up', [
            'genders' => configItem('user_settings.gender'),
        ]);
    }

    /**
     * User Sign Process.
     *
     * @param object UserSignUpRequest $request
     *
     *-----------------------------------------------------------------------*/
    public function signUpProcess(UserSignUpRequest $request)
    {
        if(getStoreSettings('allow_recaptcha')) {
            $request->validate(['g-recaptcha-response' => 'required'], [
                'g-recaptcha-response' => __tr('The recaptcha field is required.')
            ]);
        }

        $processReaction = $this->userEngine->userSignUpProcess($request->all());
        //check reaction code is 1 then redirect to login page
        if ($processReaction['reaction_code'] === 1) {
            $profileUserName = getUserAuthInfo('profile.username');
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                // $this->redirectTo('user.login')
                $profileUserName ? $this->redirectTo('user.profile_view', ['username' => getUserAuthInfo('profile.username')]) : $this->redirectTo('user.login')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Show Change Password View.
     *
     *-----------------------------------------------------------------------*/
    public function changePasswordView()
    {
        $user = Auth::user();
        $data = [];
        if ($user->password == 'NO_PASSWORD') {
            $data = [
                'userPassword' => $user->password,
            ];
        }

        return $this->loadPublicView('user.change-password', $data);
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

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Show Change Email View.
     *
     *-----------------------------------------------------------------------*/
    public function changeEmailView()
    {
        $user = Auth::user();
        $data = [
            'userEmail' => $user->email,
        ];

        return $this->loadPublicView('user.change-email', $data);
    }

    /**
     * Handle change email request.
     *
     * @param object UserChangeEmailRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function processChangeEmail(UserChangeEmailRequest $request)
    {
        $processReaction = $this->userEngine
            ->processChangeEmail(
                $request->only(
                    'new_email',
                    'current_password'
                )
            );

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Show Forgot Password View.
     *
     *-----------------------------------------------------------------------*/
    public function forgotPasswordView()
    {
        return $this->loadView('user.forgot-password');
    }

    /**
     * Handle user forgot password request.
     *
     * @param object UserForgotPasswordRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function processForgotPassword(UserForgotPasswordRequest $request)
    {
        $processReaction = $this->userEngine
            ->sendPasswordReminder(
                $request->all()
            );

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->replaceView('user.forgot-password-success', [], '.lw-success-message')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * User Sign Process.
     *
     *-----------------------------------------------------------------------*/
    public function accountActivation(Request $request, $userUid)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $processReaction = $this->userEngine->processAccountActivation($userUid);

        // Check if account activation process succeed
        if ($processReaction['reaction_code'] === 1) {
            return redirect()->route('user.login')
                ->with([
                    'success' => 'true',
                    'message' => __tr('Your account has been activated successfully. Login with your email ID and password.'),
                ]);
        }

        // if activation process failed then
        return redirect()->route('user.login')
            ->with([
                'error' => 'true',
                'message' => __tr('Account Activation link invalid.'),
            ]);
    }

    /**
     * User Sign Process.
     *
     *-----------------------------------------------------------------------*/
    public function newEmailActivation(Request $request, $userUid, $newEmail)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $processReaction = $this->userEngine->processNewEmailActivation($userUid, $newEmail);

        // Check if account activation process succeed
        if ($processReaction['reaction_code'] === 1) {
            return redirect()->route('user.new_email.read.success')->with([
                'success' => true,
                'message' => __tr('Your new email activated successfully.'),
            ]);
        }
        // if activation process failed then
        return redirect()->route('user.new_email.read.success')->with([
            'success' => false,
            'message' => __tr('Email not updated.'),
        ]);
    }

    /**
     * User Sign Process.
     *
     *-----------------------------------------------------------------------*/
    public function updateEmailSuccessView()
    {
        return $this->loadPublicView('user.change-email-success');
    }

    /**
     * Show Reset Password View.
     *
     *-----------------------------------------------------------------------*/
    public function restPasswordView()
    {
        return $this->loadManageView('user.reset-password');
    }

    /**
     * Handle reset password request.
     *
     * @param object UserResetPasswordRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function processRestPassword(
        UserResetPasswordRequest $request,
        $reminderToken
    ) {
        $processReaction = $this->userEngine
            ->processResetPassword(
                $request->all(),
                $reminderToken
            );

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('user.login')
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }

    /**
     * Get User profile view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserProfile($userName, $responseData = [])
    {
        $processReaction = $this->userEngine->prepareUserProfile($userName);
        // check if record does not exists
        if ($processReaction['reaction_code'] == 18) {
            return redirect()->route('user.profile_view', ['username' => getUserAuthInfo('profile.username')]);
        }
        $processReaction['data']['is_profile_page'] = true;

    return $this->loadPublicView('user.profile', $processReaction['data'], [
            'responseData' => $responseData
        ]);
    }

    /**
     * Get User profile view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserProfileData($userName)
    {
        $processReaction = $this->userEngine->prepareUserProfile($userName);

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
        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Get User my like view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMyLikeView()
    {
        //get page requested
        $page = request()->input('page');
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeDislikedData(1);

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.my-liked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        //load default view
        return $this->loadPublicView('user.my-liked', $processReaction['data']);
    }

    /**
     * Get User my Disliked view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMyDislikedView()
    {
        //get page requested
        $page = request()->input('page');
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeDislikedData(0);

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.my-liked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        //load default view
        return $this->loadPublicView('user.my-disliked', $processReaction['data']);
    }

    /**
     * Get User my Disliked view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getWhoLikedMeView()
    {
        //get page requested
        $page = request()->input('page');
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareUserLikeMeData();

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.my-liked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        //load default view
        return $this->loadPublicView('user.who-liked-me', $processReaction['data']);
    }

    /**
     * Get mutual like view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getMutualLikeView()
    {
        //get page requested
        $page = request()->input('page');
        //get mutual like data
        $processReaction = $this->userEngine->prepareMutualLikeData();

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.my-liked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        //load default view
        return $this->loadPublicView('user.mutual-like', $processReaction['data']);
    }

    /**
     * Get profile visitors view.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function getProfileVisitorView()
    {
        //get page requested
        $page = request()->input('page');
        //get liked people data by parameter like '1'
        $processReaction = $this->userEngine->prepareProfileVisitorsData();

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.my-liked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        //load default view
        return $this->loadPublicView('user.profile-visitor', $processReaction['data']);
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

        if ($processReaction['reaction_code'] === 1) {
            return $this->getUserProfile($processReaction['data']['username'], $processReaction['data']);
        }

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Handle report user request.
     *
     * @param object ReportUserRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function reportUser(ReportUserRequest $request, $sendUserUId)
    {
        $processReaction = $this->userEngine->processReportUser($request->all(), $sendUserUId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
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

        if ($processReaction['reaction_code'] === 1) {
            return $this->getUserProfile($processReaction['data']['username'], $processReaction['data']);
        }

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Get block user view and user list.
     *
     * @param  string  $userName
     * @return json object
     *---------------------------------------------------------------- */
    public function blockUserList()
    {
        //get page requested
        $page = request()->input('page');
        //get profile visitors data
        $processReaction = $this->userEngine->prepareBlockUserData();

        //check if page is not null and not equal to first page
        if (!is_null($page) and ($page != 1)) {
            $processReaction['data'] = view('user.partial-templates.blocked-users', $processReaction['data'])
                ->render();

            return $processReaction;
        }

        return $this->loadPublicView('user.block-user.list', $processReaction['data']);
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

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processBoostProfile()
    {
        $processReaction = $this->userEngine->processBoostProfile();

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function loadProfileUpdateWizard()
    {
        $processReaction = $this->userEngine->checkProfileStatus();

        return $this->loadView('user.profile.update-wizard', $processReaction['data']);
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function checkProfileUpdateWizard()
    {
        $processReaction = $this->userEngine->checkProfileStatus();

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * process Boost Profile.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function finishWizard()
    {
        $processReaction = $this->userEngine->finishWizard();

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * User Contact View.
     *
     *-----------------------------------------------------------------------*/
    public function getContactView()
    {
        $user = Auth::user();
        $contactData = [];
        //check is not empty
        if ($user) {
            $contactData = [
                'userFullName' => $user->first_name . ' ' . $user->last_name,
                'contactEmail' => $user->email,
            ];
        }

        return $this->loadView('user.contact', $contactData);
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

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * get booster price and period
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getBoosterInfo()
    {
        $processReaction = $this->userEngine->getBoosterInfo();

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Handle process contact request.
     *
     * @param object CommonUnsecuredPostRequest $request
     * @return json object
     *---------------------------------------------------------------- */
    public function deleteAccount(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->userEngine->processDeleteAccount($request->all());

        if ($processReaction['reaction_code'] == 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('user.login')
            );
        }

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Show login otp view.
     *---------------------------------------------------------------- */
    public function otpLoginView()
    {
        abortIf(!getStoreSettings('enable_otp_Login'));
        return $this->loadView('user.otp-login');
    }

    /**
     * Send OTP
     *
     * @param   CommonUnsecuredPostRequest
     *
     * @return  json object
     */
    public function sendOtp(CommonUnsecuredPostRequest $request)
    {
        if (is_numeric(explode('-', $request->emailOrMobile)[0])) {
            $request->validate([
                'emailOrMobile' => 'required|max:15|regex:/^\d+-\d+$/',
            ], [
                'emailOrMobile.regex' => __tr('The mobile number must be in the format of 0XX-XXXXXXXXXX'),
            ]);
        } else {
            $request->validate(['emailOrMobile' => 'required|email']);
        }
        if(getStoreSettings('allow_recaptcha')){
            $request->validate(['g-recaptcha-response' => 'required'], [
                'g-recaptcha-response' => __tr('The recaptcha field is required.')
            ]);
        }


        $processReaction = $this->userEngine->processSendOtp($request->all());
        if ($processReaction['reaction_code'] == 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('verify.otp')
            );
        }
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Show verify otp view.
     *---------------------------------------------------------------- */
    public function verifyOtpView()
    {
        return $this->loadView('user.verify-otp');
    }

    /**
     * Verify OTP
     *
     * @param   VerifyOtpRequest  $request
     *
     * @return  json object
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $processReaction = $this->userEngine->verifyOtpProcess($request->all());
        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true),
                $this->redirectTo('user.profile_view', ['username' => getUserAuthInfo('profile.username')])
            );
        } else {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [], true)
            );
        }
    }
}
