<?php

/**
 * UserEngine.php - Main component file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User;

use Auth;
use Hash;
use Session;
use YesSecurity;
use YesTokenAuth;
use Carbon\Carbon;
use PushBroadcast;
use Tzsk\Sms\Facades\Sms;
use App\Yantrana\Support\Utils;
use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Base\BaseMailer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use App\Yantrana\Support\CommonTrait;
use Illuminate\Http\Client\RequestException;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\CreditWalletEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Support\Country\Repositories\CountryRepository;
use App\Yantrana\Components\User\Repositories\LoginLogsRepository;
use App\Yantrana\Components\Item\Repositories\ManageItemRepository;
use App\Yantrana\Components\User\Repositories\CreditWalletRepository;
use App\Yantrana\Components\User\Repositories\UserEncounterRepository;
use App\Yantrana\Components\UserSetting\Repositories\UserSettingRepository;
use App\Yantrana\Components\AbuseReport\Repositories\ManageAbuseReportRepository;

class UserEngine extends BaseEngine
{
    /**
     * @var CommonTrait - Common Trait
     */
    use CommonTrait;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var BaseMailer - Base Mailer
     */
    protected $baseMailer;

    /**
     * @var  UserSettingRepository - UserSetting Repository
     */
    protected $userSettingRepository;

    /**
     * @var ManageItemRepository - ManageItem Repository
     */
    protected $manageItemRepository;

    /**
     * @var  CreditWalletRepository - CreditWallet Repository
     */
    protected $creditWalletRepository;

    /**
     * @var ManageAbuseReportRepository - ManageAbuseReport Repository
     */
    protected $manageAbuseReportRepository;

    /**
     * @var  UserEncounterRepository - UserEncounter Repository
     */
    protected $userEncounterRepository;

    /**
     * @var  CountryRepository - Country Repository
     */
    protected $countryRepository;

    /**
     * @var  MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var  LoginLogsRepository - LoginLogs Repository
     */
    protected $loginLogsRepository;

    /**
     * @var  CreditWalletEngine - CreditWallet Engine
     */
    protected $creditWalletEngine;

    /**
     * Constructor.
     *
     * @param  CreditWalletRepository  $creditWalletRepository - CreditWallet Repository
     * @param  UserRepository  $userRepository  - User Repository
     * @param  BaseMailer  $baseMailer  - Base Mailer
     * @param  UserSettingRepository  $userSettingRepository - UserSetting Repository
     * @param  ManageItemRepository  $manageItemRepository - ManageItem Repository
     * @param  CountryRepository  $countryRepository - Country Repository
     * @param  LoginLogsRepository  $loginLogsRepository - LoginLogs Repository
     * @param  CreditWalletEngine  $CreditWalletEngine - CreditWallet Engine
     *
     *-----------------------------------------------------------------------*/
    public function __construct(
        BaseMailer $baseMailer,
        UserRepository $userRepository,
        UserSettingRepository $userSettingRepository,
        ManageItemRepository $manageItemRepository,
        CreditWalletRepository $creditWalletRepository,
        ManageAbuseReportRepository $manageAbuseReportRepository,
        UserEncounterRepository $userEncounterRepository,
        CountryRepository $countryRepository,
        MediaEngine $mediaEngine,
        LoginLogsRepository $loginLogsRepository,
        CreditWalletEngine $creditWalletEngine
    ) {
        $this->baseMailer = $baseMailer;
        $this->userRepository = $userRepository;
        $this->userSettingRepository = $userSettingRepository;
        $this->manageItemRepository = $manageItemRepository;
        $this->creditWalletRepository = $creditWalletRepository;
        $this->manageAbuseReportRepository = $manageAbuseReportRepository;
        $this->userEncounterRepository = $userEncounterRepository;
        $this->countryRepository = $countryRepository;
        $this->mediaEngine = $mediaEngine;
        $this->loginLogsRepository = $loginLogsRepository;
        $this->creditWalletEngine = $creditWalletEngine;
    }

    /**
     * Process user login request using user repository & return
     * engine reaction.
     *
     * @param  array  $input
     * @return array
     *---------------------------------------------------------------- */
    public function processLogin($input)
    {
        if (getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($input)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }
        //check is email or username
        $user = $this->userRepository->fetchByEmailOrUsername($input['email_or_username']);

        // Check if empty then return error message
        if (__isEmpty($user)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('You are not a member of the system, Please go and register first , then you can proceed for login.'));
        }

        //collect login credentials
        $loginCredentials = [
            'email' => $user->email,
            'password' => $input['password'],
        ];

        //check user status not equal to 1
        if ($user->status != 1) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Your account currently __status__, Please contact to administrator.', ['__status__' => configItem('status_codes', $user->status)]));
        }

        //get remember me data
        $remember_me = (isset($input['remember_me']) and $input['remember_me'] == 'on') ? true : false;

        // Process for login attempt
        if (Auth::attempt($loginCredentials, $remember_me)) {
            // Clear login attempts of ip address
            $this->userRepository->clearLoginAttempts();
            //loggedIn user name
            $loggedInUserName = $user->first_name . ' ' . $user->last_name;
            //get people likes me data
            $userLikedMeData = $this->userRepository->fetchUserLikeMeData();
            //check user like data exists
            $notifyUserUids = [];
            if (!__isEmpty($userLikedMeData)) {
                foreach ($userLikedMeData as $userLike) {
                    if (getUserSettings('show_user_login_notification', $user->userId)) {
                        $notifyUserUids[] = $userLike->userUId;
                    }
                }
                //push data to pusher
                PushBroadcast::notifyViaPusher('event.user.notification', $notifyUserUids, [
                    'type' => 'user-login',
                    // 'userUid' => $userLike->userUId,
                    'subject' => __tr('User Logged In successfully'),
                    'message' => __tr('__loggedInUserName__ is online now', [
                        '__loggedInUserName__' => $loggedInUserName
                    ]),
                    'messageType' => 'success', // __tr('success'),
                    // 'showNotification' => getUserSettings('show_user_login_notification', $userLike->userId),
                    'showNotification' => true,
                    // 'getNotificationList' => getNotificationList($userLike->userId),
                    'getNotificationList' => [
                        'notificationData' => [],
                        // to prevent notification update count on client side
                        // 'notificationCount' => -1,
                        'notificationCount' => '',
                    ]
                ]);
            }
            // $this->creditWalletEngine->processToShowCreditBonus($user);
            if(!$this->loginLogsRepository->fetchIt(['user_id' => $user->_id])) {
                $this->loginLogsRepository->createLoginLog($user);
            }
            //if mobile request
            if (isMobileAppRequest()) {
                //issue new token
                $authToken = YesTokenAuth::issueToken([
                    'aud' => $user->_id,
                    'uaid' => $user->user_authority_id,
                ]);

                return $this->engineReaction(1, [
                    'auth_info' => getUserAuthInfo(1),
                    'access_token' => $authToken,
                ], __tr('Welcome, you are logged in successfully.'));
            }

            return $this->engineReaction(1, [
                'auth_info' => getUserAuthInfo(1),
                'intendedUrl' => Session::get('intendedUrl'),
                'show_message' => true,
            ], __tr('Welcome, you are logged in successfully.'));
        }

        // Store every login attempt.
        $this->userRepository->updateLoginAttempts();

        return $this->engineReaction(2, ['show_message' => true], __tr('Authentication failed, please check your credentials & try again.'));
    }

    /**
     * Get Auth Info
     *
     * @return void
     */
    public function authInfo()
    {
        return $this->engineReaction(1, [
            'auth_info' => getUserAuthInfo(1),
            'show_message' => false,
        ]);
    }

    /**
     * Process logout request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processLogout()
    {
        if (Session::has('intendedUrl')) {
            Session::forget('intendedUrl');
        }

        if (isset($_SESSION['CURRENT_LOCALE'])) {
            $_SESSION['CURRENT_LOCALE'] = null;
        }

        $userId = Auth::user()->_id;

        //fetch user authority
        $userAuthority = $this->userRepository->fetchUserAuthority($userId);

        //update data
        $updateData = [
            'updated_at' => Carbon::now()->subMinutes(2)->toDateTimeString(),
        ];

        // Check for if new email activation store
        if ((!__isEmpty($userAuthority)) and $this->userRepository->updateUserAuthority($userAuthority, $updateData)) {
            Auth::logout();
        } else {
            Auth::logout();
        }

        return $this->engineReaction(2, null, __tr('User logout failed.'));
    }

    /**
     * Process App logout request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processAppLogout()
    {
        Auth::logout();

        return $this->engineReaction(1, ['auth_info' => getUserAuthInfo()], 'logout Successfully');
    }

    /**
     * User Sign prepare
     *-----------------------------------------------------------------------*/
    public function prepareSignupData()
    {
        $allGenders = configItem('user_settings.gender');
        $genders = [];
        foreach ($allGenders as $key => $value) {
            $genders[] = [
                'id' => $key,
                'value' => $value,
            ];
        }

        return $this->engineReaction(1, [
            'privacy_policy_url' => getStoreSettings('privacy_policy_url'),
            'terms_and_conditions_url' => getStoreSettings('terms_and_conditions_url'),
            'age_restrictions' => [
                'min' => getAgeDate(configItem('age_restriction.maximum'), 'max')->format('Y-m-d'),
                'max' => getAgeDate(configItem('age_restriction.minimum'))->format('Y-m-d')
            ],
            'genders' => $genders,
            'country_phone_codes' => getCountryPhoneCodes(),
        ]);
    }

    /**
     * User Sign Process.
     *
     * @param  array  $inputData
     *
     *-----------------------------------------------------------------------*/
    public function userSignUpProcess($inputData)
    {
        // Verify recaptcha
        if (getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($inputData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }

        $transactionResponse = $this->userRepository->processTransaction(function () use ($inputData) {
            $activationRequiredForNewUser = getStoreSettings('activation_required_for_new_user');
            $inputData['status'] = 1; // Active
            // check if activation is required for new user
            if ($activationRequiredForNewUser) {
                $inputData['status'] = 4; // Never Activated
            }
            // Store user
            $newUser = $this->userRepository->storeUser($inputData);
            // Check if user not stored successfully
            if (!$newUser) {
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('User not added.'));
            }
            $userAuthorityData = [
                'user_id' => $newUser->_id,
                'user_roles__id' => 2,
            ];
            // Add user authority
            if ($this->userRepository->storeUserAuthority($userAuthorityData)) {
                //check enable bonus credits for new user
                if (getStoreSettings('enable_bonus_credits')) {
                    $creditWalletStoreData = [
                        'status' => 1,
                        'users__id' => $newUser->_id,
                        'credits' => getStoreSettings('number_of_credits'),
                        'credit_type' => 1, //Bonuses
                    ];
                    //store user credit transaction data
                    if (!$this->userRepository->storeCreditWalletTransaction($creditWalletStoreData)) {
                        return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('User credits not stored.'));
                    }
                }

                $profileData = [
                    'users__id' => $newUser->_id,
                    'gender' => $inputData['gender'],
                    'dob' => $inputData['dob'],
                    'status' => 1,
                ];

                //store profile
                if ($this->userRepository->storeUserProfile($profileData)) {
                    //check activation required for new users
                    if ($activationRequiredForNewUser) {
                        $emailData = [
                            'fullName' => $newUser->first_name,
                            'email' => $newUser->email,
                            'expirationTime' => configItem('account.expiry'),
                            'activation_url' => URL::temporarySignedRoute('user.account.activation', Carbon::now()->addHours(configItem('account.expiry')), ['userUid' => $newUser->_uid]),
                        ];
                        // check if email send to member
                        if ($this->baseMailer->notifyToUser('Your account registered successfully.', 'account.activation', $emailData, $newUser->email)) {
                            return $this->userRepository->transactionResponse(1, [
                                'show_message' => true,
                                'activation_required' => true,
                            ], __tr('Your account created successfully, to activate your account please check your email.'));
                        }
                        return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Failed to send activation email, please try again later.'));
                    } else {
                        $this->userRepository->transactionResponse(1, ['show_message' => true], __tr('Your account created successfully.'));

                        $loginCredentials = [
                            'email' => $inputData['email'],
                            'password' => $inputData['password'],
                        ];
                        $user = $this->userRepository->fetchByEmailOrUsername($inputData['email']);
                        //get remember me data
                        $remember_me = (isset($input['remember_me']) and $input['remember_me'] == 'on') ? true : false;

                        if (Auth::attempt($loginCredentials, $remember_me)) {
                            //if mobile request
                            if (isMobileAppRequest()) {
                                //issue new token
                                $authToken = YesTokenAuth::issueToken([
                                    'aud' => $user->_id,
                                    'uaid' => $user->user_authority_id,
                                ]);

                                return $this->userRepository->transactionResponse(1, [
                                    'activation_required' => false,
                                    'auth_info' => getUserAuthInfo(1),
                                    'access_token' => $authToken,
                                ], 'Welcome, you are logged in successfully.');
                            }

                            if (getStoreSettings('send_welcome_email_to_newly_registered_users')) {
                                $this->userWelcomeNotifyMail($newUser);
                            }
                            return $this->userRepository->transactionResponse(1, [
                                'auth_info' => getUserAuthInfo(1),
                                'intendedUrl' => Session::get('intendedUrl'),
                                'show_message' => true,
                            ], __tr('Welcome, you are logged in successfully.'));
                        }
                        // Store every login attempt.
                        $this->userRepository->updateLoginAttempts();

                        return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Authentication failed, please check your credentials & try again.'));
                    }
                }
            }
            // Send failed server error message
            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Something went wrong on server, please contact to administrator.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Process user update password request.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processUpdatePassword($inputData)
    {
        $user = Auth::user();
        // Check if logged in user password matched with entered password
        if (!Hash::check($inputData['current_password'], $user->password)) {
            return $this->engineReaction(3, ['show_message' => true], __tr('Current password is incorrect.'));
        }

        // Check if user password updated
        if ($this->userRepository->updatePassword($user, $inputData['new_password'])) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Password updated successfully'));
        }

        return $this->engineReaction(14, ['show_message' => true], __tr('Password not updated.'));
    }

    /**
     * Send new email activation reminder.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processChangeEmail($inputData)
    {
        $user = Auth::user();
        // Check if user entered correct password or not
        if (!Hash::check($inputData['current_password'], $user->password)) {
            return $this->engineReaction(3, ['show_message' => true], __tr('Please check your password.'));
        }
        //get data
        $activationRequired = getStoreSettings('activation_required_for_change_email');

        //check activation required or not
        if ($activationRequired) {
            $emailData = [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'newEmail' => $inputData['new_email'],
                'expirationTime' => configItem('account.change_email_expiry'),
                'activation_url' => URL::temporarySignedRoute('user.new_email.activation', Carbon::now()->addHours(configItem('account.change_email_expiry')), ['userUid' => $user->_uid, 'newEmail' => $inputData['new_email']]),

            ];
            // check if email send to member
            if ($this->baseMailer->notifyToUser('New Email Activation.', 'account.new-email-activation', $emailData, $inputData['new_email'])) {
                return $this->engineReaction(1, ['show_message' => true, 'activationRequired' => true], __tr('New email activation link has been sent to your new email address, please check your email.'));
            } else {
                return $this->engineReaction(2, ['show_message' => true], __tr('Failed to send confirmation email.'));
            }
        } else {
            $updateData = [
                'email' => $inputData['new_email'],
            ];
            // Check for if new email activation store
            if ($this->userRepository->updateUser($user, $updateData)) {
                return $this->engineReaction(1, [
                    'show_message' => true,
                    'activationRequired' => false,
                    'newEmail' => $inputData['new_email'],
                ], __tr('Update email successfully.'));
            }
        }
        //error response
        return $this->engineReaction(2, ['show_message' => true], __tr('Email not updated.'));
    }

    /**
     * Activate new email.
     *
     * @param  number  $userID
     * @return array
     *---------------------------------------------------------------- */
    public function processNewEmailActivation($userUid, $newEmail)
    {
        $user = $this->userRepository->fetch($userUid);
        // Check if user record exist
        if (__isEmpty($user)) {
            return $this->engineReaction(2, null, __tr('User data not exists.'));
        }
        $updateData = [
            'email' => $newEmail,
        ];

        // Check for if new email activation store
        if ($this->userRepository->updateUser($user, $updateData)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Update email successfully.'));
        }
        //error response
        return $this->engineReaction(2, ['show_message' => true], __tr('Email not updated.'));
    }

    /**
     * Process forgot password request based on passed email address &
     * send password reminder on enter email address.
     *
     * @param  string  $email
     * @return array
     *---------------------------------------------------------------- */
    public function sendPasswordReminder($inputData)
    {
        // Verify recaptcha
        if (getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($inputData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }
        $email = $inputData['email'];
        $user = $this->userRepository->fetchActiveUserByEmail($email);

        // Check if user record exist
        if (__isEmpty($user)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Request.'));
        }

        // Delete old password reminder for this user
        $this->userRepository->deleteOldPasswordReminder($email);

        $token = YesSecurity::generateUid();
        $createdAt = getCurrentDateTime();

        $storeData = [
            'email' => $email,
            'token' => $token,
            'created_at' => $createdAt,
        ];

        // Check for if password reminder added
        if (!$this->userRepository->storePasswordReminder($storeData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Request.'));
        }

        //message data
        $emailData = [
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'expirationTime' => config('__tech.account.password_reminder_expiry'),
            'email' => $user->email,
            'email' => $user->email,
            'email' => $user->email,
            'tokenUrl' => route('user.reset_password', ['reminderToken' => $token]),
        ];

        // if reminder mail has been sent
        if ($this->baseMailer->notifyToUser('Forgot Password.', 'account.password-reminder', $emailData, $user->email)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('We have e-mailed your password reset link.')); // success reaction
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server')); // error reaction
    }

    /**
     * Process reset password request.
     *
     * @param  array  $input
     * @param  string  $reminderToken
     * @return array
     *---------------------------------------------------------------- */
    public function processResetPassword($input, $reminderToken)
    {
        $email = $input['email'];

        //check if mobile app request then change request Url
        $token = $reminderToken;

        //get password reminder count
        $count = $this->userRepository->fetchPasswordReminderCount($token, $email);

        // Check if reminder count not exist on 0
        if (!$count > 0) {
            return  $this->engineReaction(18, ['show_message' => true], __tr('Invalid Request.'));
        }

        //fetch active user by email
        $user = $this->userRepository->fetchActiveUserByEmail($email);

        // Check if user record exist
        if (__isEmpty($user)) {
            return  $this->engineReaction(18, ['show_message' => true], __tr('Invalid Request.'));
        }

        // Check if user password updated
        if ($this->userRepository->resetPassword($user, $input['password'])) {
            return  $this->engineReaction(1, ['show_message' => true], __tr('Password reset successfully.'));
        }

        //failed response
        return  $this->engineReaction(2, ['show_message' => true], __tr('Password not updated.'));
    }

    /**
     * Process Account Activation.
     *
     * @param  string  $userUid
     *
     *-----------------------------------------------------------------------*/
    public function processAccountActivation($userUid)
    {
        $neverActivatedUser = $this->userRepository->fetchNeverActivatedUser($userUid);

        // Check if never activated user exist or not
        if (__isEmpty($neverActivatedUser)) {
            return $this->engineReaction(18, null, __tr('Account Activation link invalid.'));
        }

        $updateData = [
            'status' => 1, // Active
        ];
        // Check if user activated successfully
        if ($this->userRepository->updateUser($neverActivatedUser, $updateData)) {
            if (getStoreSettings('send_welcome_email_to_newly_registered_users')) {
                $this->userWelcomeNotifyMail($neverActivatedUser);
            }
            return $this->engineReaction(1, null, __tr('Your account has been activated successfully. Login with your email ID and password.'));
        }

        return  $this->engineReaction(2, null, __tr('Account Activation link invalid.'));
    }

    /**
     * Prepare User Profile Data.
     *
     * @param  string  $userName
     *
     *-----------------------------------------------------------------------*/
    public function prepareUserProfile($userName)
    {
        // fetch User by username
        $user = $this->userRepository->fetchByUsername($userName, true);

        // check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, [], __tr('User does not exists.'));
        }
        $userId = $user->_id;
        $userUid = $user->_uid;
        $loggedInUserId = getUserID();
        $isOwnProfile = ($userId == $loggedInUserId) ? true : false;
        $loggedInUserIsPremium = isPremiumUser();
        $loggedInUser = Auth::user();
        // $withoutCodeMblNo = explode('-', $user->mobile_number);
        // $mobileNumber = isset($withoutCodeMblNo[1]) ? $withoutCodeMblNo[1] : '';
        // $countryCode = explode('0',$withoutCodeMblNo[0]);
        // $countryPhnCode = isset($countryCode[1]) ? ($countryCode[1]) : '';
        $explodeMobileNumber = explodeMobileNumber($user->mobile_number);

        // Prepare user data
        $userData = [
            'userId' => $userId,
            'userUId' => $userUid,
            'fullName' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile_number' => $explodeMobileNumber['mobile_number'],
            'userName' => $user->username,
            'country_code' => $explodeMobileNumber['country_code'],
        ];

        $userProfileData = $userSpecifications = $userSpecificationData = $photosData = [];

        // fetch User details
        $userProfile = $this->userSettingRepository->fetchUserProfile($userId);
        $userSettingConfig = configItem('user_settings');
        $profilePictureFolderPath = getPathByKey('profile_photo', ['{_uid}' => $userUid]);
        $profilePictureUrl = noThumbImageURL();
        $coverPictureFolderPath = getPathByKey('cover_photo', ['{_uid}' => $userUid]);
        $coverPictureUrl = noThumbCoverImageURL();
        // Check if user profile exists
        if (!__isEmpty($userProfile)) {
            if (!__isEmpty($userProfile->profile_picture)) {
                $profilePictureUrl = getMediaUrl($profilePictureFolderPath, $userProfile->profile_picture);
            }
            if (!__isEmpty($userProfile->cover_picture)) {
                $coverPictureUrl = getMediaUrl($coverPictureFolderPath, $userProfile->cover_picture);
            }
        }
        // Set cover and profile picture url
        $userData['profilePicture'] = $profilePictureUrl;
        $userData['coverPicture'] = $coverPictureUrl;
        $userData['userAge'] = isset($userProfile->dob) ? Carbon::parse($userProfile->dob)->age : null;

        // check if user profile exists
        if (!\__isEmpty($userProfile)) {
            // Get country name
            $countryName = '';
            if (!__isEmpty($userProfile->countries__id)) {
                $country = $this->countryRepository->fetchById($userProfile->countries__id, ['name']);
                $countryName = $country->name;
            }

            //fetch user liked data by to user id
            $peopleILikeUserIds = $this->userRepository->fetchMyLikeDataByUserId($user->_id)->pluck('to_users__id')->toArray();

            $showMobileNumber = true;
            //check login user exist then don't apply this condition.
            if ($user->_id != getUserID()) {
                //check admin can set true mobile number not display of user
                if (getStoreSettings('display_mobile_number') == 1) {
                    $showMobileNumber = false;
                }
                //check admin can set user choice user can show or not mobile number
                if (getStoreSettings('display_mobile_number') == 2 and getUserSettings('display_user_mobile_number', $user->_id) == 1) {
                    $showMobileNumber = false;
                }
                //check admin can set user choice and user can select people I liked user
                if (getStoreSettings('display_mobile_number') == 2 and getUserSettings('display_user_mobile_number', $user->_id) == 2 and !in_array($loggedInUserId, $peopleILikeUserIds)) {
                    $showMobileNumber = false;
                }
            }

            $userProfileData = [
                'aboutMe' => $userProfile->about_me,
                'city' => $userProfile->city,
                'mobile_number' => $user->mobile_number,
                'showMobileNumber' => $showMobileNumber,
                'gender' => $userProfile->gender,
                'gender_text' => array_get($userSettingConfig, 'gender.' . $userProfile->gender),
                'country' => $userProfile->countries__id,
                'country_name' => $countryName,
                'dob' => $userProfile->dob,
                'birthday' => (!\__isEmpty($userProfile->dob))
                    ? formatDate($userProfile->dob)
                    : '',
                'work_status' => $userProfile->work_status,
                'formatted_work_status' => array_get($userSettingConfig, 'work_status.' . $userProfile->work_status),
                'education' => $userProfile->education,
                'formatted_education' => array_get($userSettingConfig, 'educations.' . $userProfile->education),
                'preferred_language' => $userProfile->preferred_language,
                'formatted_preferred_language' => array_get($userSettingConfig, 'preferred_language.' . $userProfile->preferred_language),
                'relationship_status' => $userProfile->relationship_status,
                'formatted_relationship_status' => array_get($userSettingConfig, 'relationship_status.' . $userProfile->relationship_status),
                'latitude' => $userProfile->location_latitude,
                'longitude' => $userProfile->location_longitude,
                'isVerified' => $userProfile->is_verified,
            ];
        }

        $specificationCollection = $this->userSettingRepository->fetchUserSpecificationById($userId);
        // Check if user specifications exists
        if (!\__isEmpty($specificationCollection)) {
            $userSpecifications = $specificationCollection->pluck('specification_value', 'specification_key')->toArray();
        }
        $specificationConfig = $this->getUserSpecificationConfig();
        foreach ($specificationConfig['groups'] as $specKey => $specification) {
            $items = [];
            if (!isset($specification['items'])) {
                $specification['items'] = [];
            }
            if (isset($specification['status']) and $specification['status'] == 0) {
                continue;
            }
            foreach ($specification['items'] as $itemKey => $item) {
                if (!$isOwnProfile and array_key_exists($itemKey, $userSpecifications)) {
                    $userSpecKey = $userSpecifications[$itemKey];
                    $items[] = [
                        'name' => $itemKey,
                        'label' => $item['name'],
                        'input_type' => $item['input_type'],
                        'value' => (isset($item['options']) and isset($item['options'][$userSpecKey]))
                            ? $item['options'][$userSpecKey]
                            : $userSpecifications[$itemKey],
                        'options' => isset($item['options']) ? $item['options'] : '',
                        'selected_options' => (!__isEmpty($userSpecKey)) ? $userSpecKey : '',
                    ];
                } elseif ($isOwnProfile) {
                    $itemValue = '';
                    $userSpecValue = isset($userSpecifications[$itemKey])
                        ? $userSpecifications[$itemKey]
                        : '';
                    if (!__isEmpty($userSpecValue)) {
                        $itemValue = isset($item['options'])
                            ? (isset($item['options'][$userSpecValue])
                                ? $item['options'][$userSpecValue] : '')
                            : $userSpecValue;
                    }
                    $items[] = [
                        'name' => $itemKey,
                        'label' => $item['name'],
                        'input_type' => $item['input_type'],
                        'value' => $itemValue,
                        'options' => isset($item['options']) ? $item['options'] : '',
                        'selected_options' => $userSpecValue,
                    ];
                }
            }
            // Check if Item exists
            if (!__isEmpty($items)) {
                $userSpecificationData[$specKey] = [
                    'title' => $specification['title'],
                    'icon' => $specification['icon'],
                    'items' => $items,
                ];
            }
        }
        // __dd($userSpecificationData);

        // Get user photos collection
        $userPhotosCollection = $this->userSettingRepository->fetchUserPhotos($userId);
        $userPhotosFolderPath = getPathByKey('user_photos', ['{_uid}' => $userUid]);
        // check if user photos exists
        if (!__isEmpty($userPhotosCollection)) {
            foreach ($userPhotosCollection as $userPhoto) {
                $photosData[] = [
                    'image_url' => getMediaUrl($userPhotosFolderPath, $userPhoto->file),
                ];
            }
        }

        //fetch like dislike data by to user id
        $likeDislikeData = $this->userRepository->fetchLikeDislike($user->_id);

        $userLikeData = [];
        //check is not empty
        if (!__isEmpty($likeDislikeData)) {
            $userLikeData = [
                '_id' => $likeDislikeData->_id,
                'like' => $likeDislikeData->like,
            ];
        }

        //check loggedIn User id doesn't match current user id then
        // store visitor profile data
        if ($userId != getUserID()) {
            $profileVisitorData = $this->userRepository->fetProfileVisitorByUserId($userId);
            //check is empty then store profile visitor data
            if (__isEmpty($profileVisitorData)) {
                $storeData = [
                    'status' => 1,
                    'to_users__id' => $userId,
                    'by_users__id' => $loggedInUserId,
                ];

                //store profile visitors data
                if ($this->userRepository->storeProfileVisitors($storeData)) {
                    //user full name
                    $userFullName = $user->first_name . ' ' . $user->last_name;

                    //activity log message
                    activityLog($userFullName . ' ' . 'profile visited.');

                    //loggedIn user name
                    $loggedInUserName = $loggedInUser->first_name . ' ' . $loggedInUser->last_name;
                    //check user browser
                    $allowVisitorProfile = getFeatureSettings('browse_incognito_mode');
                    //check in setting allow visitor notification log and pusher request
                    if (!$allowVisitorProfile) {
                        //notification log message
                        notificationLog('Profile visited by' . ' ' . $loggedInUserName, route('user.profile_view', ['username' => $loggedInUser->username]), null, $userId);
                        //push data to pusher
                        PushBroadcast::notifyViaPusher('event.user.notification', [
                            'type' => 'profile-visitor',
                            'userUid' => $userUid,
                            'subject' => __tr('Profile visited successfully'),
                            'message' => __tr('Profile visited by') . ' ' . $loggedInUserName,
                            'messageType' => __tr('success'),
                            'showNotification' => getUserSettings('show_visitor_notification', $user->_id),
                            'getNotificationList' => getNotificationList($user->_id),
                        ]);
                    }
                } else {
                    return $this->engineReaction(18, [], __tr('Profile visitors not created.'));
                }
            }
        }

        //fetch total visitors data
        $visitorData = $this->userRepository->fetchProfileVisitor($userId);

        //fetch gift collection
        $giftCollection = $this->manageItemRepository->fetchListData(1);

        $giftListData = [];
        if (!__isEmpty($giftCollection)) {
            foreach ($giftCollection as $key => $giftData) {
                //only active gifts
                if ($giftData->status == 1) {
                    $giftImageUrl = '';
                    $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $giftData->_uid]);
                    $giftImageUrl = getMediaUrl($giftImageFolderPath, $giftData->file_name);
                    //get normal price or normal price is zero then show free gift
                    $normalPrice = (isset($giftData['normal_price']) and intval($giftData['normal_price']) <= 0) ? 'Free' : intval($giftData['normal_price']) . ' ' . __tr('credits');

                    //get premium price or premium price is zero then show free gift
                    $premiumPrice = (isset($giftData['premium_price']) and $giftData['premium_price'] <= 0) ? 'Free' : $giftData['premium_price'] . ' ' . __tr('credits');
                    $giftData['premium_price'] . ' ' . __tr('credits');

                    $price = 'Free';
                    //check user is premium or normal or Set price
                    if ($loggedInUserIsPremium) {
                        $price = $premiumPrice;
                    } else {
                        $price = $normalPrice;
                    }
                    $giftListData[] = [
                        '_id' => $giftData['_id'],
                        '_uid' => $giftData['_uid'],
                        'normal_price' => $normalPrice,
                        'premium_price' => $giftData['premium_price'],
                        'formattedPrice' => $price,
                        'gift_image_url' => $giftImageUrl,
                    ];
                }
            }
        }

        //fetch user gift record
        $userGiftCollection = $this->userRepository->fetchUserGift($userId);

        $userGiftData = [];
        //check if not empty
        if (!__isEmpty($userGiftCollection)) {
            foreach ($userGiftCollection as $key => $userGift) {
                $userGiftImgUrl = '';
                $userGiftFolderPath = getPathByKey('gift_image', ['{_uid}' => $userGift->itemUId]);
                $userGiftImgUrl = getMediaUrl($userGiftFolderPath, $userGift->file_name);
                //check gift status is private (1) and check gift send to current user or gift send by current user
                if ($userGift->status == 1 and ($userGift->to_users__id == $loggedInUserId || $userGift->from_users__id == $loggedInUserId)) {
                    $userGiftData[] = [
                        '_id' => $userGift->_id,
                        '_uid' => $userGift->_uid,
                        'itemId' => $userGift->itemId,
                        'status' => $userGift->status,
                        'fromUserName' => $userGift->fromUserName,
                        'senderUserName' => $userGift->senderUserName,
                        'userGiftImgUrl' => $userGiftImgUrl,
                        'isPrivate' => true,
                    ];
                //check gift status is public (0)
                } elseif ($userGift->status != 1) {
                    $userGiftData[] = [
                        '_id' => $userGift->_id,
                        '_uid' => $userGift->_uid,
                        'itemId' => $userGift->itemId,
                        'status' => $userGift->status,
                        'fromUserName' => $userGift->fromUserName,
                        'senderUserName' => $userGift->senderUserName,
                        'userGiftImgUrl' => $userGiftImgUrl,
                        'isPrivate' => false,
                    ];
                }
            }
        }

        //fetch block me users
        $blockMeUser = $this->userRepository->fetchBlockMeUser($user->_id);
        $isBlockUser = false;
        //check if not empty then set variable is true
        if (!__isEmpty($blockMeUser)) {
            $isBlockUser = true;
        }

        //fetch block by me user
        $blockUserData = $this->userRepository->fetchBlockUser($user->_id);
        $blockByMe = false;
        //if it is empty
        if (!__isEmpty($blockUserData)) {
            $blockByMe = true;
        }

        return $this->engineReaction(1, [
            'isOwnProfile' => $isOwnProfile,
            'userData' => $userData,
            'countries' => $this->countryRepository->fetchAll()->toArray(),
            'genders' => $userSettingConfig['gender'],
            'preferredLanguages' => $userSettingConfig['preferred_language'],
            'relationshipStatuses' => $userSettingConfig['relationship_status'],
            'workStatuses' => $userSettingConfig['work_status'],
            'educations' => $userSettingConfig['educations'],
            'userProfileData' => $userProfileData,
            'photosData' => $photosData,
            'userSpecificationData' => $userSpecificationData,
            'userLikeData' => $userLikeData,
            'totalUserLike' => fetchTotalUserLikedCount($userId),
            'totalVisitors' => $visitorData->count(),
            'isBlockUser' => $isBlockUser,
            'blockByMeUser' => $blockByMe,
            'giftListData' => $giftListData,
            'userGiftData' => $userGiftData,
            'userOnlineStatus' => $this->getUserOnlineStatus($user->userAuthorityUpdatedAt),
            'isPremiumUser' => isPremiumUser($userId),
        ]);
    }

    /**
     * User Like Dislike Process.
     *
     * @param  array  $inputData
     *
     *-----------------------------------------------------------------------*/
    public function processUserLikeDislike($toUserUid, $like, $optionalLoggedInUserId = null)
    {
        // fetch User by toUserUid
        $user = $this->userRepository->fetch($toUserUid);

        // check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('User does not exists.'));
        }

        //delete old encounter User
        $this->userEncounterRepository->deleteOldEncounterUser();
        if (!__isEmpty($optionalLoggedInUserId) and isAdmin()) {
            $loggedInUser = $this->userRepository->fetch($optionalLoggedInUserId);
        } else {
            $loggedInUser = Auth::user();
        }

        //user full name
        $userFullName = $user->first_name . ' ' . $user->last_name;
        //loggedIn user name
        $loggedInUserFullName = $loggedInUser->first_name . ' ' . $loggedInUser->last_name;
        $loggedInUserName = $loggedInUser->username;
        $showLikeNotification = getUserSettings('show_like_notification', $user->_id);

        //fetch like dislike data by to user id
        $likeDislikeData = $this->userRepository->fetchLikeDislike($user->_id, $optionalLoggedInUserId);

        //check if not empty
        if (!__isEmpty($likeDislikeData)) {
            //if user already liked then show error messages
            if ($like == $likeDislikeData['like'] and $like == 1) {
                if ($this->userRepository->deleteLikeDislike($likeDislikeData)) {
                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 1,
                        'status' => 'deleted',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User Liked Removed Successfully'));
                }
            } elseif ($like == $likeDislikeData['like'] and $like == 0) {
                if ($this->userRepository->deleteLikeDislike($likeDislikeData)) {
                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 2,
                        'status' => 'deleted',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User Disliked Removed Successfully'));
                }
            }

            //update data
            $updateData = ['like' => $like];
            //update like dislike
            if ($this->userRepository->updateLikeDislike($likeDislikeData, $updateData)) {
                //is like 1
                if ($like == 1) {
                    //activity log message
                    activityLog($userFullName . ' ' . 'profile liked.');
                    //notification log message
                    notificationLog('Profile liked by' . ' ' . $loggedInUserFullName, route('user.profile_view', ['username' => $loggedInUserName]), null, $user->_id);
                    //check show like feature return true
                    if ($showLikeNotification) {
                        //push data to pusher
                        PushBroadcast::notifyViaPusher('event.user.notification', [
                            'type' => 'user-likes',
                            'userUid' => $user->_uid,
                            'subject' => __tr('User liked successfully'),
                            'message' => __tr('Profile liked by') . ' ' . $loggedInUserFullName,
                            'messageType' => 'success',
                            'showNotification' => getUserSettings('show_like_notification', $user->_id),
                            'getNotificationList' => getNotificationList($user->_id),
                        ]);
                    }

                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 1,
                        'status' => 'updated',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User liked successfully.'));
                } else {
                    //activity log message
                    activityLog($userFullName . ' ' . 'profile Disliked.');

                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 2,
                        'status' => 'updated',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User Disliked successfully.'));
                }
            }
        } else {
            //store data
            $storeData = [
                'status' => 1,
                'to_users__id' => $user->_id,
                'by_users__id' => __isEmpty($optionalLoggedInUserId) ? getUserID() : $optionalLoggedInUserId,
                'like' => $like,
            ];
            //store like dislike
            if ($this->userRepository->storeLikeDislike($storeData)) {
                //is like 1
                if ($like == 1) {
                    //activity log message
                    activityLog($userFullName . ' ' . 'profile liked.');
                    //check show like feature return true
                    if ($showLikeNotification) {
                        //notification log message
                        notificationLog('Profile liked by' . ' ' . $loggedInUserFullName, route('user.profile_view', ['username' => $loggedInUserName]), null, $user->_id);

                        //push data to pusher
                        PushBroadcast::notifyViaPusher('event.user.notification', [
                            'type' => 'user-likes',
                            'userUid' => $user->_uid,
                            'subject' => __tr('User liked successfully'),
                            'message' => __tr('Profile liked by') . ' ' . $loggedInUserFullName,
                            'messageType' => 'success',
                            'showNotification' => getUserSettings('show_like_notification', $user->_id),
                            'getNotificationList' => getNotificationList($user->_id),
                        ]);
                    }

                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 1,
                        'status' => 'created',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User liked successfully.'));
                } else {
                    //activity log message
                    activityLog($userFullName . ' ' . 'profile Disliked.');

                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'likeStatus' => 2,
                        'status' => 'created',
                        'totalLikes' => fetchTotalUserLikedCount($user->_id),
                    ], __tr('User Disliked successfully.'));
                }
            }
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong.'));
    }

    /**
     * Prepare User Liked Data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareUserLikeDislikedData($likeType)
    {
        //fetch user liked data by to user id
        $likedCollection = $this->userRepository->fetchUserLikeData($likeType, true);

        return $this->engineReaction(1, [
            'totalCount' => $likedCollection->total(),
            'usersData' => $this->prepareUserArray($likedCollection),
            'nextPageUrl' => $likedCollection->nextPageUrl(),
        ]);
    }

    /**
     * Prepare User Liked Me Data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareUserLikeMeData()
    {
        $showWhoLikesMe = getFeatureSettings('show_like');
        if (!$showWhoLikesMe) {
            return $this->engineReaction(1, [
                'userRequestType' => 'who_liked_me',
                'totalCount' => 0,
                'usersData' => [],
                'nextPageUrl' => '',
                'showWhoLikeMeUser' => $showWhoLikesMe,
            ]);
        }
        //get people likes me data
        $userLikedMeData = $this->userRepository->fetchUserLikeMeData(true);
        return $this->engineReaction(1, [
            'userRequestType' => 'who_liked_me',
            'totalCount' => $userLikedMeData->total(),
            'usersData' => $this->prepareUserArray($userLikedMeData),
            'nextPageUrl' => $userLikedMeData->nextPageUrl(),
            'showWhoLikeMeUser' => $showWhoLikesMe,
        ]);
    }

    /**
     * Prepare Mutual like data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareMutualLikeData()
    {
        //fetch user liked data by to user id
        $likedCollection = $this->userRepository->fetchUserLikeData(1, false);
        //pluck people like ids
        $peopleLikeUserIds = $likedCollection->pluck('to_users__id')->toArray();
        //get people likes me data
        $userLikedMeData = $this->userRepository->fetchUserLikeMeData(false)->whereIn('by_users__id', $peopleLikeUserIds);
        //pluck people like me ids
        $mutualLikeIds = $userLikedMeData->pluck('_id')->toArray();
        //get mutual like data
        $mutualLikeCollection = $this->userRepository->fetchMutualLikeUserData($mutualLikeIds);

        return $this->engineReaction(1, [
            'userRequestType' => 'mutual_likes',
            'totalCount' => $mutualLikeCollection->total(),
            'usersData' => $this->prepareUserArray($mutualLikeCollection),
            'nextPageUrl' => $mutualLikeCollection->nextPageUrl(),
        ]);
    }

    /**
     * Prepare profile visitors Data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareProfileVisitorsData()
    {
        //profile boost all user list
        $isPremiumUser = $this->userRepository->fetchAllPremiumUsers();
        //premium user ids
        $premiumUserIds = $isPremiumUser->pluck('users__id')->toArray();
        //get profile visitor data
        $profileVisitors = $this->userRepository->fetchProfileVisitorData($premiumUserIds);

        $userData = [];
        //check if not empty collection
        if (!__isEmpty($profileVisitors)) {
            foreach ($profileVisitors as $key => $user) {
                //check user browser
                $allowVisitorProfile = getFeatureSettings('browse_incognito_mode', null, $user->userId);

                //check is premium user value is false and in array check premium user exists
                //then data not shown in visitors page
                if (!$allowVisitorProfile and !in_array($user->userId, $premiumUserIds)) {
                    $userImageUrl = '';
                    //check is not empty
                    if (!__isEmpty($user->profile_picture)) {
                        $profileImageFolderPath = getPathByKey('profile_photo', ['{_uid}' => $user->userUId]);
                        $userImageUrl = getMediaUrl($profileImageFolderPath, $user->profile_picture);
                    } else {
                        $userImageUrl = noThumbImageURL();
                    }

                    $userCoverUrl = '';
                    if (!__isEmpty($user->cover_picture)) {
                        $profileImageFolderPath = getPathByKey('cover_photo', ['{_uid}' => $user->userUId]);
                        $userCoverUrl = getMediaUrl($profileImageFolderPath, $user->cover_picture);
                    } else {
                        $userCoverUrl = noThumbCoverImageURL();
                    }

                    $userAge = isset($user->dob) ? Carbon::parse($user->dob)->age : null;
                    $gender = isset($user->gender) ? configItem('user_settings.gender', $user->gender) : null;

                    $userData[] = [
                        '_id' => $user->userId,
                        '_uid' => $user->userUId,
                        'status' => $user->status,
                        'like' => $user->like,
                        'created_at' => formatDiffForHumans($user->created_at),
                        'updated_at' => formatDiffForHumans($user->updated_at),
                        'userFullName' => $user->userFullName,
                        'username' => $user->username,
                        'userImageUrl' => $userImageUrl,
                        'userCoverUrl' => $userCoverUrl,
                        'profilePicture' => $user->profile_picture,
                        'userOnlineStatus' => $this->getUserOnlineStatus($user->userAuthorityUpdatedAt),
                        'gender' => $gender,
                        'dob' => $user->dob,
                        'userAge' => $userAge,
                        'countryName' => $user->countryName,
                        'isPremiumUser' => isPremiumUser($user->userId),
                        'detailString' => implode(', ', array_filter([__tr($userAge), $gender])),
                    ];
                }
            }
        }

        return $this->engineReaction(1, [
            'totalCount' => $profileVisitors->total(),
            'usersData' => $userData,
            'nextPageUrl' => $profileVisitors->nextPageUrl(),
        ]);
    }

    /**
     * Prepare User Array Data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareUserArray($userCollection)
    {
        $userData = [];
        //check if not empty collection
        if (!__isEmpty($userCollection)) {
            foreach ($userCollection as $key => $user) {
                $userImageUrl = '';
                //check is not empty
                if (!__isEmpty($user->profile_picture)) {
                    $profileImageFolderPath = getPathByKey('profile_photo', ['{_uid}' => $user->userUId]);
                    $userImageUrl = getMediaUrl($profileImageFolderPath, $user->profile_picture);
                } else {
                    $userImageUrl = noThumbImageURL();
                }

                $userCoverUrl = '';
                if (!__isEmpty($user->cover_picture)) {
                    $profileImageFolderPath = getPathByKey('cover_photo', ['{_uid}' => $user->userUId]);
                    $userCoverUrl = getMediaUrl($profileImageFolderPath, $user->cover_picture);
                } else {
                    $userCoverUrl = noThumbCoverImageURL();
                }

                $userAge = isset($user->dob) ? Carbon::parse($user->dob)->age : '';
                $gender = isset($user->gender) ? configItem('user_settings.gender', $user->gender) : '';

                $userData[] = [
                    '_id' => $user->userId,
                    '_uid' => $user->userUId,
                    'status' => $user->status,
                    'like' => $user->like,
                    'created_at' => formatDiffForHumans($user->created_at),
                    'updated_at' => formatDiffForHumans($user->updated_at),
                    'userFullName' => $user->userFullName,
                    'username' => $user->username,
                    'userImageUrl' => $userImageUrl,
                    'userCoverUrl' => $userCoverUrl,
                    'profilePicture' => $user->profile_picture,
                    'userOnlineStatus' => $this->getUserOnlineStatus($user->userAuthorityUpdatedAt),
                    'gender' => $gender,
                    'dob' => $user->dob,
                    'userAge' => $userAge,
                    'countryName' => $user->countryName,
                    'isPremiumUser' => isPremiumUser($user->userId),
                    'detailString' => implode(', ', array_filter([__tr($userAge), $gender])),
                ];
            }
        }

        return $userData;
    }

    /**
     * Process User Send Gift.
     *
     *-----------------------------------------------------------------------*/
    public function processUserSendGift($inputData, $sendUserUId)
    {
        //buy premium plan request
        $userSendGiftRequest = $this->userRepository->processTransaction(function () use ($inputData, $sendUserUId) {
            //fetch user
            $user = $this->userRepository->fetch($sendUserUId);

            //if user not exists
            if (__isEmpty($user)) {
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('User does not exists.'));
            }

            //fetch gift data
            $giftData = $this->manageItemRepository->fetch($inputData['selected_gift']);

            //if gift not exists
            if (__isEmpty($giftData)) {
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Gift data does not exists.'));
            }

            //fetch user credits data
            $totalUserCredits = totalUserCredits();
            //check user is premium or normal or Set price
            if (isPremiumUser()) {
                $credits = $giftData->premium_price;
            } else {
                $credits = $giftData->normal_price;
            }

            //if gift price greater then total user credits then show error message
            if ($credits > $totalUserCredits) {
                return $this->userRepository->transactionResponse(2, [
                    'show_message' => true,
                    'errorType' => 'insufficient_balance',
                ], __tr('Your credit balance is too low, please purchase credits.'));
            }

            //credit wallet store data
            $creditWalletStoreData = [
                'status' => 1,
                'users__id' => getUserID(),
                'credits' => '-' . '' . $credits,
            ];

            //store user gift data
            if ($creditWalledId = $this->userRepository->storeCreditWalletTransaction($creditWalletStoreData)) {
                //store gift data
                $giftStoreData = [
                    'status' => (isset($inputData['isPrivateGift'])
                        and $inputData['isPrivateGift'] == 'on') ? 1 : 0,
                    'from_users__id' => getUserID(),
                    'to_users__id' => $user->_id,
                    'items__id' => $giftData->_id,
                    'price' => $giftData->normal_price,
                    'credit_wallet_transactions__id' => $creditWalledId,
                ];

                //store gift data
                if ($this->userRepository->storeUserGift($giftStoreData)) {
                    $userFullName = $user->first_name . ' ' . $user->last_name;
                    activityLog($userFullName . ' ' . 'send gift.');
                    //loggedIn user name
                    $loggedInUserName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    //notification log message
                    notificationLog('Gift send by' . ' ' . $loggedInUserName, route('user.profile_view', ['username' => Auth::user()->username]), null, $user->_id);

                    //push data to pusher
                    PushBroadcast::notifyViaPusher('event.user.notification', [
                        'type' => 'user-gift',
                        'userUid' => $user->_uid,
                        'subject' => __tr('Gift send successfully'),
                        'message' => __tr('Gift send by') . ' ' . $loggedInUserName,
                        'messageType' => 'success',
                        'showNotification' => getUserSettings('show_gift_notification', $user->_id),
                        'getNotificationList' => getNotificationList($user->_id),
                    ]);

                    return $this->userRepository->transactionResponse(1, [
                        'message' => __tr('Gift Sent.'),
                        'creditsRemaining' => totalUserCredits(),
                        'username' => $user->username,
                        'giftUid' => $giftData->_uid,
                    ], __tr('Gift Sent.'));
                }
            }
            //error message
            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Gift not send.'));
        });

        //response
        return $this->engineReaction($userSendGiftRequest);
    }

    /**
     * Process Report User.
     *
     *-----------------------------------------------------------------------*/
    public function processReportUser($inputData, $sendUserUId)
    {
        //fetch user
        $user = $this->userRepository->fetch($sendUserUId);

        //if user not exists
        if (__isEmpty($user)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('User does not exists.'));
        }

        //fetch reported user data
        $reportUserData = $this->manageAbuseReportRepository->fetchAbuseReport($user->_id);

        //if exist then throw error message
        if (!__isEmpty($reportUserData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('You already reported this user for abuse.'));
        }

        //store report data
        $storeReportData = [
            'status' => 1,
            'for_users__id' => $user->_id,
            'by_users__id' => getUserID(),
            'reason' => $inputData['report_reason'],
        ];
        // store report data
        if ($this->manageAbuseReportRepository->storeReportUser($storeReportData)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('User abuse reported successfully.'));
        }

        //error message
        return $this->engineReaction(1, ['show_message' => true], __tr('User failed to abuse report.'));
    }

    /**
     * Process Block User.
     *
     *-----------------------------------------------------------------------*/
    public function processBlockUser($inputData)
    {
        //fetch user
        $user = $this->userRepository->fetch($inputData['block_user_id']);

        //if user not exists
        if (__isEmpty($user)) {
            return $this->engineReaction(2, null, __tr('User does not exists.'));
        }

        //fetch block user data
        $blockUserData = $this->userRepository->fetchBlockUser($user->_id);

        //check is not empty
        if (!__isEmpty($blockUserData)) {
            //error response
            return $this->engineReaction(2, null, __tr('You are already blocked this user.'));
        }

        //store data
        $storeData = [
            'status' => 1,
            'to_users__id' => $user->_id,
            'by_users__id' => getUserID(),
        ];

        //store block user data
        if ($this->userRepository->storeBlockUser($storeData)) {
            //user full name
            $userFullName = $user->first_name . ' ' . $user->last_name;
            $loggedInUser = Auth::user();
            //loggedIn user name
            $loggedInUserName = $loggedInUser->first_name . ' ' . $loggedInUser->last_name;
            //activity log message
            activityLog($loggedInUserName . ' ' . 'blocked by.' . ' ' . $userFullName);

            //success response
            return $this->engineReaction(1, [
                'show_message' => true,
                'message' => __tr('User Blocked Successfully.'),
                'username' => $user->username
            ], __tr('User Blocked Successfully.'));
        }
        //error response
        return $this->engineReaction(2, [
            'show_message' => true,
        ], __tr('Failed to block the user.'));
    }

    /**
     * Prepare Block User Data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareBlockUserData()
    {
        $blockUserCollection = $this->userRepository->fetchAllBlockUser(true);

        $blockUserList = [];
        //check if not empty
        if (!__isEmpty($blockUserCollection)) {
            foreach ($blockUserCollection as $key => $blockUser) {
                $userImageUrl = '';
                //check is not empty
                if (!__isEmpty($blockUser->profile_picture)) {
                    $profileImageFolderPath = getPathByKey('profile_photo', ['{_uid}' => $blockUser->userUId]);
                    $userImageUrl = getMediaUrl($profileImageFolderPath, $blockUser->profile_picture);
                } else {
                    $userImageUrl = noThumbImageURL();
                }
                $userCoverUrl = '';
                if (!__isEmpty($blockUser->cover_picture)) {
                    $profileImageFolderPath = getPathByKey('cover_photo', ['{_uid}' => $blockUser->userUId]);
                    $userCoverUrl = getMediaUrl($profileImageFolderPath, $blockUser->cover_picture);
                } else {
                    $userCoverUrl = noThumbCoverImageURL();
                }

                $userAge = isset($blockUser->dob) ? Carbon::parse($blockUser->dob)->age : null;
                $gender = isset($blockUser->gender) ? configItem('user_settings.gender', $blockUser->gender) : null;

                $blockUserList[] = [
                    '_id' => $blockUser->_id,
                    '_uid' => $blockUser->_uid,
                    'userId' => $blockUser->userId,
                    'userUId' => $blockUser->userUId,
                    'userFullName' => $blockUser->userFullName,
                    'status' => $blockUser->status,
                    'created_at' => formatDiffForHumans($blockUser->created_at),
                    'userOnlineStatus' => $this->getUserOnlineStatus($blockUser->userAuthorityUpdatedAt),
                    'username' => $blockUser->username,
                    'userImageUrl' => $userImageUrl,
                    'userCoverUrl' => $userCoverUrl,
                    'profilePicture' => $blockUser->profile_picture,
                    'gender' => $gender,
                    'dob' => $blockUser->dob,
                    'userAge' => $userAge,
                    'countryName' => $blockUser->countryName,
                    'isPremiumUser' => isPremiumUser($blockUser->userId),
                    'detailString' => implode(', ', array_filter([__tr($userAge), $gender])),
                ];
            }
        }

        //success reaction
        return $this->engineReaction(1, [
            'userRequestType' => 'blocked_users',
            'totalCount' => $blockUserCollection->total(),
            'usersData' => $blockUserList,
            'nextPageUrl' => $blockUserCollection->nextPageUrl(),
        ]);
    }

    /**
     *Process unblock user.
     *
     *-----------------------------------------------------------------------*/
    public function processUnblockUser($userUid)
    {
        //fetch user
        $user = $this->userRepository->fetch($userUid);

        //if user not exists
        if (__isEmpty($user)) {
            return $this->engineReaction(2, null, __tr('User does not exists.'));
        }

        //fetch block user data
        $blockUserData = $this->userRepository->fetchBlockUser($user->_id);

        //if it is empty
        if (__isEmpty($blockUserData)) {
            return $this->engineReaction(2, null, __tr('Block user does not exists.'));
        }

        //delete block user
        if ($this->userRepository->deleteBlockUser($blockUserData)) {
            //user full name
            $userFullName = $user->first_name . ' ' . $user->last_name;
            //loggedIn user name
            $loggedInUserName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
            //activity log message
            activityLog($loggedInUserName . ' ' . 'Unblock by.' . ' ' . $userFullName);
            //success response
            return $this->engineReaction(1, [
                'blockUserUid' => $blockUserData->_uid,
                'blockUserLength' => $this->userRepository->fetchAllBlockUser()->count(),
            ], __tr('User has been unblock successfully.'));
        }

        //error response
        return $this->engineReaction(2, null, __tr('Failed to unblock user.'));
    }

    /**
     *	Process Boost Profile
     *
     *-----------------------------------------------------------------------*/
    public function processBoostProfile()
    {
        $transactionResponse = $this->userRepository->processTransaction(function () {
            $user = Auth::user();

            //fetch user
            $activeBoost = $this->userRepository->fetchActiveProfileBoost($user->_id);

            $remainingTime = 0;

            if (!__isEmpty($activeBoost)) {
                $remainingTime = Carbon::now()->diffInSeconds($activeBoost->expiry_at, false);
            }

            $totalUserCredits = totalUserCredits();
            $boostPeriod = getStoreSettings('booster_period');
            $boostPrice = getStoreSettings('booster_price');

            if (isPremiumUser()) {
                $boostPrice = getStoreSettings('booster_price_for_premium_user');
            }

            if ($totalUserCredits < $boostPrice) {
                return $this->userRepository->transactionResponse(2, [
                    'show_message' => true,
                    'creditsRemaining' => totalUserCredits(),
                    'insufficientCredits' => true,
                ], __tr('Enough credits are not available. Please buy some credits'));
            }

            //credit wallet store data
            $creditWalletStoreData = [
                'status' => 1,
                'users__id' => $user->_id,
                'credits' => '-' . '' . $boostPrice,
            ];

            //store user gift data
            if ($creditWalledId = $this->userRepository->storeCreditWalletTransaction($creditWalletStoreData)) {
                $boosterData = [
                    'for_users__id' => $user->_id,
                    'expiry_at' => Carbon::now()->addSeconds(($remainingTime + ($boostPeriod * 60))),
                    'status' => 1,
                    'credit_wallet_transactions__id' => $creditWalledId,
                ];

                if ($booster = $this->userRepository->storeBooster($boosterData)) {
                    //activity log message
                    activityLog(strtr('Booster activated by user __firstName__ __lastName__', [
                        '__firstName__' => $user->first_name,
                        '__lastName__' => $user->last_name,
                    ]));

                    //fetch user
                    $activeBoost = $this->userRepository->fetchActiveProfileBoost($user->_id);

                    //success response
                    return $this->userRepository->transactionResponse(1, [
                        'show_message' => true,
                        'boosterExpiry' => Carbon::now()->diffInSeconds($activeBoost->expiry_at, false),
                        'creditsRemaining' => totalUserCredits(),
                    ], __tr('Booster activated successfully'));
                }
            }

            //error response
            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Failed to boost profile.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     *	Check profile status
     *
     *-----------------------------------------------------------------------*/
    public function checkProfileStatus()
    {
        //get profile
        $userProfile = $this->userSettingRepository->fetchUserProfile(getUserID());

        if (__isEmpty($userProfile)) {
            $userProfile = $this->userRepository->storeUserProfile([
                'users__id' => getUserID(),
                'status' => 1,
            ]);
        }

        $steps = configItem('profile_update_wizard');

        if ($userProfile->status == 2) {
            $profileStatus = [
                'step_one' => true,
                'step_two' => true,
            ];
        } else {
            //check if co-ordinates are set
            if ((__isEmpty($userProfile['location_longitude'])
                    or $userProfile['location_longitude'] == 0)
                and (__isEmpty($userProfile['location_latitude'])
                    or $userProfile['location_latitude'] == 0)
            ) {
                $profileStatus['step_two'] = false;
            } else {
                $profileStatus['step_two'] = true;
            }

            //for step one
            $profileStatus['step_one'] = $this->isStepCompleted($userProfile->toArray(), $steps['step_one']);
        }

        //preview options
        $profileInfo = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'gender' => $userProfile['gender'],
            'birthday' => $userProfile['dob'],
            'location_longitude' => isset($userProfile['location_longitude']) ? floatval($userProfile['location_longitude']) : null,
            'location_latitude' => isset($userProfile['location_latitude']) ? floatval($userProfile['location_latitude']) : null,
        ];

        $userUID = authUID();

        //profile pic
        if (isset($userProfile['profile_picture']) and !__isEmpty($userProfile['profile_picture'])) {
            //path
            $profilePictureFolderPath = getPathByKey('profile_photo', ['{_uid}' => $userUID]);
            // url
            $profileInfo['profile_picture_url'] = getMediaUrl($profilePictureFolderPath, $userProfile['profile_picture']);
        }

        //cover photo
        if (isset($userProfile['cover_picture']) and !__isEmpty($userProfile['cover_picture'])) {
            //path
            $coverPictureFolderPath = getPathByKey('cover_photo', ['{_uid}' => $userUID]);
            // url
            $profileInfo['cover_picture_url'] = getMediaUrl($coverPictureFolderPath, $userProfile['cover_picture']);
        }

        return $this->engineReaction(1, [
            'profileStatus' => $profileStatus,
            'profileInfo' => $profileInfo,
            'genders' => configItem('user_settings.gender'),
            'profileMediaRestriction' => getMediaRestriction('profile'),
            'coverImageMediaRestriction' => getMediaRestriction('cover_image'),
        ]);
    }

    /**
     *	Check profile status
     *
     *-----------------------------------------------------------------------*/
    public function finishWizard()
    {
        //get profile
        $userProfile = $this->userSettingRepository->fetchUserProfile(getUserID());

        if ($this->userRepository->updateProfile($userProfile, ['status' => 2]) || $userProfile->status == 2) {
            return $this->engineReaction(1, [
                'redirectURL' => route('user.profile_view', ['username' => getUserAuthInfo('profile.username')]),
            ], __tr('Wizard completed successfully'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Failed to complete profile'));
    }

    /**
     *	check if step completed
     *
     *-----------------------------------------------------------------------*/
    private function isStepCompleted($profile, $stepFields)
    {
        if (!__isEmpty($profile)) {
            foreach ($profile as $key => $value) {
                if (in_array($key, $stepFields)) {
                    if (__isEmpty($profile[$key])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Process user contact request.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processContact($inputData)
    {
        //contact email data
        $emailData = [
            'userName' => $inputData['fullName'],
            'senderEmail' => $inputData['email'],
            'toEmail' => getStoreSettings('contact_email'),
            'subject' => $inputData['subject'],
            'messageText' => $inputData['message'],
        ];

        if (getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($inputData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }

        // check if email send to member
        if ($this->baseMailer->notifyAdmin($inputData['subject'], 'contact', $emailData, 2)) {
            //success response
            return $this->engineReaction(1, ['show_message' => true], __tr('Mail has been sent successfully, we contact soon.'));
        }
        //error response
        return $this->engineReaction(2, ['show_message' => true], __tr('Failed to send mail.'));
    }

    public function checkRecaptcha($inputData)
    {
        // if is mobile request ignore reCaptcha
        if (isMobileAppRequest()) {
            return true;
        }
        $recaptcha_token = $inputData['g-recaptcha-response'];
        try {
            // Make a POST request to the reCAPTCHA verification endpoint
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => getStoreSettings('recaptcha_secret_key'),
                'response' => $recaptcha_token, // The token generated by the reCAPTCHA client-side library
                'remoteip' => request()->ip(), // The IP address of the user submitting the reCAPTCHA
            ]);
            $responseData = $response->json();

            // Check if the verification was successful
            if (isset($responseData['success']) and $responseData['success'] == 1) {
                return true;
            } else {
                return false;
            }
        } catch (RequestException $e) {
            // An error occurred while making the request
            // Handle the error here
            return false;
        }
    }

    /**
     * Process user contact request.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function getBoosterInfo()
    {
        return $this->engineReaction(1, [
            'remaining_booster_time' => getProfileBoostTime(),
            'booster_period' => __tr(getStoreSettings('booster_period')),
            'booster_price' => __tr((isPremiumUser()) ? getStoreSettings('booster_price_for_premium_user') : getStoreSettings('booster_price')),
        ]);
    }

    /**
     * Process delete account.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processDeleteAccount($inputData)
    {
        // Check if user exists
        if (isAdmin()) {
            return $this->engineReaction(18, ['show_message' => true], __tr('Admin can not be deleted.'));
        }

        $user = $this->userRepository->fetchByID(getUserID());

        // Check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }

        if (!Hash::check($inputData['password'], $user->password)) {
            return $this->engineReaction(3, ['show_message' => true], __tr('Current password is incorrect.'));
        }

        // Delete all media of user
        $deletedMedia = $this->mediaEngine->deleteUserAccount();

        // Delete account successfully
        if ($this->userRepository->deleteUser($user)) {
            // log debug entry
            activityLog($user->username . ' ' . 'deleted himself.');
            // Process Logout user
            $this->processLogout();

            return $this->engineReaction(1, ['show_message' => true], __tr('Your account has been deleted successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Account not deleted.'));
    }

    /**
     * Process delete account.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function resendActivationMail($inputData)
    {
        $user = $this->userRepository->fetchByEmail($inputData['email']);

        // Check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('You are not a member of this system.'));
        }

        // Check if user exists
        if ($user->status == 1) {
            return $this->engineReaction(18, ['show_message' => true], __tr('Account already activated.'));
        }

        $transactionResponse = $this->userRepository->processTransaction(function () use ($user) {
            if ($updatedUser = $this->userRepository->updateUser($user, [
                'remember_token' => Utils::generateStrongPassword(4, false, 'ud'),
            ])) {
                $emailData = [
                    'fullName' => $user->first_name,
                    'email' => $user->email,
                    'expirationTime' => configItem('otp_expiry'),
                    'otp' => $updatedUser->remember_token,
                ];

                // check if email send to member
                if ($this->baseMailer->notifyToUser('Activation mail sent.', 'account.activation-for-app', $emailData, $user->email)) {
                    return $this->userRepository->transactionResponse(1, [
                        'show_message' => true,
                        'activation_mail_sent' => true,
                    ], __tr('Activation mail sent successfully, to activate your account please check your email.'));
                }

                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Failed to send activation mail'));
            }
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Process verify otp
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    // public function verifyOtpProcess($inputData, $type)
    // {
    //     // exit;
    //     $user = $this->userRepository->fetchByEmail($inputData['email']);

    //     // Check if user exists
    //     if (__isEmpty($user)) {
    //         return $this->engineReaction(18, ['show_message' => true], __tr('You are not a member of this system.'));
    //     }

    //     $transactionResponse = $this->userRepository->processTransaction(function () use ($inputData, $user, $type) {
    //         if ($type == 1) {
    //             $neverActivatedUser = $this->userRepository->fetchNeverActivatedUserForApp($inputData['email']);

    //             // Check if never activated user exist or not
    //             if (__isEmpty($neverActivatedUser)) {
    //                 return $this->userRepository->transactionResponse(18, null, __tr('Invalid OTP'));
    //             }

    //             if ($user->remember_token == $inputData['otp']) {
    //                 $updatedUser = $this->userRepository->updateUser($user, ['remember_token' => null, 'status' => 1]);

    //                 return $this->userRepository->transactionResponse(1, [
    //                     'show_message' => true,
    //                 ], __tr('Otp verified successfully.'));
    //             }
    //         } elseif ($type == 2) {
    //             $passwordReset = $this->userRepository->fetchPasswordReset($inputData['otp']);

    //             if (__isEmpty($passwordReset)) {
    //                 return $this->userRepository->transactionResponse(18, null, __tr('Invalid OTP'));
    //             }

    //             return $this->userRepository->transactionResponse(1, [
    //                 'show_message' => true,
    //                 'account_verified' => true,
    //             ], __tr('OTP verified successfully. Set your new Password'));
    //         }

    //         return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Invalid OTP'));
    //     });

    //     return $this->engineReaction($transactionResponse);
    // }

    /**
     * Process delete account.
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function requestNewPassword($inputData)
    {
        $user = $this->userRepository->fetchByEmail($inputData['email']);

        // Check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('You are not a member of this system.'));
        }

        // Check if user exists
        if ($user->status != 1) {
            return $this->engineReaction(18, [
                'show_message' => true,
            ], __tr('Your account might be Inactive, Blocked or Not Activated.'));
        }

        $transactionResponse = $this->userRepository->processTransaction(function () use ($inputData, $user) {
            // Delete old password reminder for this user
            $this->userRepository->appDeleteOldPasswordReminder($inputData['email']);

            $currentDateTime = Carbon::now();
            $token = Utils::generateStrongPassword(4, false, 'ud');
            $createdAt = $currentDateTime->addSeconds(configItem('otp_expiry'));

            $storeData = [
                'email' => $inputData['email'],
                'token' => $token,
                'created_at' => $createdAt,
            ];

            // Check for if password reminder added
            if (!$this->userRepository->storePasswordReminder($storeData)) {
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Invalid Request.'));
            }

            $otpExpiry = configItem('otp_expiry');
            $differenceSeconds = Carbon::now()->diffInSeconds($createdAt, false);
            $newExpiryTime = 0;
            if ($differenceSeconds > 0 and $differenceSeconds < $otpExpiry) {
                $newExpiryTime = $differenceSeconds;
            }

            $emailData = [
                'fullName' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'expirationTime' => config('__tech.account.app_password_reminder_expiry'),
                'otp' => $token,
            ];

            // check if email send to member
            if ($this->baseMailer->notifyToUser('OTP Verification', 'account.forgot-password-for-app', $emailData, $user->email)) {
                return $this->userRepository->transactionResponse(1, [
                    'show_message' => true,
                    'mail_sent' => true,
                    'newExpiryTime' => $newExpiryTime,
                ], __tr('OTP sent successfully, to reset password use OTP sent to your email.'));
            }

            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Failed to send OTP'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Process Forgot Password resend otp request
     *
     * @param $userEmail array - userEmail data
     * @return json object
     */
    public function processForgotPasswordResendOtp($userEmail)
    {
        $transactionResponse = $this->userRepository->processTransaction(function () use ($userEmail) {
            $user = $this->userRepository->fetchActiveUserByEmail($userEmail);

            // Check if empty then return error message
            if (__isEmpty($user)) {
                return $this->userRepository->transactionResponse(2, null, 'You are not a member of the system, Please go and register first , then you can proceed for login.');
            }

            // Delete old password reminder for this user
            $this->userRepository->appDeleteOldPasswordReminder($user->email);

            //check if mobile app request then change request Url
            $currentDateTime = Carbon::now();
            $token = Utils::generateStrongPassword(4, false, 'ud');
            $createdAt = $currentDateTime->addSeconds(configItem('otp_expiry'));

            $storeData = [
                'email' => $user->email,
                'token' => $token,
                'created_at' => $createdAt,
            ];

            // Check for if password reminder added
            if (!$this->userRepository->storePasswordReminder($storeData)) {
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Invalid Request.'));
            }

            $emailData = [
                'fullName' => $user->first_name,
                'email' => $user->email,
                'expirationTime' => config('__tech.account.app_password_reminder_expiry'),
                'otp' => $token,
            ];

            $otpExpiry = configItem('otp_expiry');
            $differenceSeconds = Carbon::now()->diffInSeconds($createdAt, false);
            $newExpiryTime = 0;
            if ($differenceSeconds > 0 and $differenceSeconds < $otpExpiry) {
                $newExpiryTime = $differenceSeconds;
            }

            // check if email send to member
            if ($this->baseMailer->notifyToUser('OTP Verification', 'account.forgot-password-for-app', $emailData, $user->email)) {
                return $this->userRepository->transactionResponse(1, [
                    'show_message' => true,
                    'mail_sent' => true,
                    'newExpiryTime' => $newExpiryTime,
                ], __tr('OTP sent successfully, to reset password use OTP sent to your email.'));
            }

            return $this->userRepository->transactionResponse(2, null, 'Invalid Request'); // error reaction
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Process reset password request.
     *
     * @param  array  $input
     * @param  string  $reminderToken
     * @return array
     *---------------------------------------------------------------- */
    public function resetPasswordForApp($input)
    {
        $email = $input['email'];

        //fetch active user by email
        $user = $this->userRepository->fetchActiveUserByEmail($email);

        // Check if user record exist
        if (__isEmpty($user)) {
            return  $this->engineReaction(18, ['show_message' => true], __tr('Invalid Request.'));
        }

        // Check if user password updated
        if ($this->userRepository->resetPassword($user, $input['password'])) {
            return  $this->engineReaction(1, [
                'show_message' => true,
                'password_changed' => true,
            ], __tr('Password reset successfully.'));
        }

        //failed response
        return  $this->engineReaction(2, ['show_message' => true], __tr('Password not updated.'));
    }

    /**
     * prepare profile details
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareProfileDetails($username)
    {
        // fetch User by username
        $user = $this->userRepository->fetchByUsername($username, true);

        // Check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }

        $userId = $user->_id;
        $userUid = $user->_uid;
        $loggedInUserId = getUserID();
        $isOwnProfile = ($userId == $loggedInUserId) ? true : false;
        $loggedInUserIsPremium = isPremiumUser();
        $loggedInUser = Auth::user();

        // mobile number show logic

        $showMobileNumber = true;
        //check login user exist then don't apply this condition.
        if ($userId != $loggedInUserId) {
            //fetch user liked data by to user id
            $peopleILikeUserIds = $this->userRepository->fetchMyLikeDataByUserId($userId)->pluck('to_users__id')->toArray();
            //check admin can set true mobile number not display of user
            if (getStoreSettings('display_mobile_number') == 1) {
                $showMobileNumber = false;
            }
            //check admin can set user choice user can show or not mobile number
            if (getStoreSettings('display_mobile_number') == 2 and getUserSettings('display_user_mobile_number', $userId) == 1) {
                $showMobileNumber = false;
            }
            //check admin can set user choice and user can select people I liked user
            if (getStoreSettings('display_mobile_number') == 2 and getUserSettings('display_user_mobile_number', $userId) == 2 and !in_array($loggedInUserId, $peopleILikeUserIds)) {
                $showMobileNumber = false;
            }
        }
        // /mobile number show logic

        // Prepare user data
        $userData = [
            'userId' => $userId,
            'userUId' => $userUid,
            'fullName' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile_number' => $showMobileNumber ? $user->mobile_number : 'XXXXXXXXXX',
            'userName' => $user->username,
            'userOnlineStatus' => $this->getUserOnlineStatus($user->userAuthorityUpdatedAt),
        ];

        $userProfileData = $userSpecifications = $userSpecificationData = $photosData = [];

        // fetch User details
        $userProfile = $this->userSettingRepository->fetchUserProfile($userId);
        $userSettingConfig = configItem('user_settings');
        $profilePictureFolderPath = getPathByKey('profile_photo', ['{_uid}' => $userUid]);
        $profilePictureUrl = noThumbImageURL();
        $coverPictureFolderPath = getPathByKey('cover_photo', ['{_uid}' => $userUid]);
        $coverPictureUrl = noThumbCoverImageURL();

        // Check if user profile exists
        if (!__isEmpty($userProfile)) {
            if (!__isEmpty($userProfile->profile_picture)) {
                $profilePictureUrl = getMediaUrl($profilePictureFolderPath, $userProfile->profile_picture);
            }
            if (!__isEmpty($userProfile->cover_picture)) {
                $coverPictureUrl = getMediaUrl($coverPictureFolderPath, $userProfile->cover_picture);
            }
        }

        // Set cover and profile picture url
        $userData['profilePicture'] = $profilePictureUrl;
        $userData['coverPicture'] = $coverPictureUrl;
        $userData['userAge'] = isset($userProfile->dob) ? Carbon::parse($userProfile->dob)->age : null;

        // check if user profile exists
        if (!__isEmpty($userProfile)) {
            // Get country name
            $countryName = '';
            if (!__isEmpty($userProfile->countries__id)) {
                $country = $this->countryRepository->fetchById($userProfile->countries__id, ['name']);
                $countryName = $country->name;
            }
            $userProfileData = [
                'aboutMe' => $userProfile->about_me,
                'city' => $userProfile->city,
                'mobile_number' => $showMobileNumber ? $user->mobile_number : 'XXXXXXXXXX',
                'gender' => $userProfile->gender,
                'gender_text' => array_get($userSettingConfig, 'gender.' . $userProfile->gender),
                'country' => $userProfile->countries__id,
                'country_name' => $countryName,
                'dob' => $userProfile->dob,
                'birthday' => (!\__isEmpty($userProfile->dob))
                    ? formatDate($userProfile->dob)
                    : '',
                'work_status' => $userProfile->work_status,
                'formatted_work_status' => array_get($userSettingConfig, 'work_status.' . $userProfile->work_status),
                'education' => $userProfile->education,
                'formatted_education' => array_get($userSettingConfig, 'educations.' . $userProfile->education),
                'preferred_language' => $userProfile->preferred_language,
                'formatted_preferred_language' => array_get($userSettingConfig, 'preferred_language.' . $userProfile->preferred_language),
                'relationship_status' => $userProfile->relationship_status,
                'formatted_relationship_status' => array_get($userSettingConfig, 'relationship_status.' . $userProfile->relationship_status),
                'latitude' => isset($userProfile->location_latitude) ? floatval($userProfile->location_latitude) : null,
                'longitude' => isset($userProfile->location_longitude) ? floatval($userProfile->location_longitude) : null,
                'isVerified' => $userProfile->is_verified,
            ];
        }

        // Get user photos collection
        $userPhotosCollection = $this->userSettingRepository->fetchUserPhotos($userId);
        $userPhotosFolderPath = getPathByKey('user_photos', ['{_uid}' => $userUid]);
        // check if user photos exists
        if (!__isEmpty($userPhotosCollection)) {
            foreach ($userPhotosCollection as $userPhoto) {
                $photosData[] = [
                    'image_url' => getMediaUrl($userPhotosFolderPath, $userPhoto->file),
                ];
            }
        }

        //check loggedIn User id doesn't match current user id then
        // store visitor profile data
        if ($userId != $loggedInUserId) {
            $profileVisitorData = $this->userRepository->fetProfileVisitorByUserId($userId);
            //check is empty then store profile visitor data
            if (__isEmpty($profileVisitorData)) {
                $storeData = [
                    'status' => 1,
                    'to_users__id' => $userId,
                    'by_users__id' => $loggedInUserId,
                ];

                //store profile visitors data
                if ($this->userRepository->storeProfileVisitors($storeData)) {
                    //user full name
                    $userFullName = $user->first_name . ' ' . $user->last_name;

                    //activity log message
                    activityLog($userFullName . ' ' . 'profile visited.');

                    //loggedIn user name
                    $loggedInUserName = $loggedInUser->first_name . ' ' . $loggedInUser->last_name;
                    //check user browser
                    $allowVisitorProfile = getFeatureSettings('browse_incognito_mode');
                    //check in setting allow visitor notification log and pusher request
                    if (!$allowVisitorProfile) {
                        //notification log message
                        notificationLog('Profile visited by' . ' ' . $loggedInUserName, route('user.profile_view', ['username' => $loggedInUser->username]), null, $userId);
                        //push data to pusher
                        PushBroadcast::notifyViaPusher('event.user.notification', [
                            'type' => 'profile-visitor',
                            'userUid' => $userUid,
                            'subject' => __tr('Profile visited successfully'),
                            'message' => __tr('Profile visited by') . ' ' . $loggedInUserName,
                            'messageType' => __tr('success'),
                            'showNotification' => getUserSettings('show_visitor_notification', $user->_id),
                            'getNotificationList' => getNotificationList($user->_id),
                        ]);
                    }
                } else {
                    return $this->engineReaction(18, [], __tr('Profile visitors not created.'));
                }
            }
        }

        //fetch total visitors data
        $visitorData = $this->userRepository->fetchProfileVisitor($userId);

        //fetch user gift record
        $userGiftCollection = $this->userRepository->fetchUserGift($userId);

        $userGiftData = [];
        //check if not empty
        if (!__isEmpty($userGiftCollection)) {
            foreach ($userGiftCollection as $key => $userGift) {
                $userGiftImgUrl = '';
                $userGiftFolderPath = getPathByKey('gift_image', ['{_uid}' => $userGift->itemUId]);
                $userGiftImgUrl = getMediaUrl($userGiftFolderPath, $userGift->file_name);
                //check gift status is private (1) and check gift send to current user or gift send by current user
                if ($userGift->status == 1 and ($userGift->to_users__id == $loggedInUserId || $userGift->from_users__id == $loggedInUserId)) {
                    if (__isEmpty($userGift->file_name)) {
                        $userGiftImgUrl = noThumbImageURL();
                    }

                    $userGiftData[] = [
                        '_id' => $userGift->_id,
                        '_uid' => $userGift->_uid,
                        'itemId' => $userGift->itemId,
                        'status' => $userGift->status,
                        'fromUserName' => $userGift->fromUserName,
                        'senderUserName' => $userGift->senderUserName,
                        'userGiftImgUrl' => $userGiftImgUrl,
                        'isPrivate' => true,
                    ];
                //check gift status is public (0)
                } elseif ($userGift->status != 1) {
                    if (__isEmpty($userGift->file_name)) {
                        $userGiftImgUrl = noThumbImageURL();
                    }

                    $userGiftData[] = [
                        '_id' => $userGift->_id,
                        '_uid' => $userGift->_uid,
                        'itemId' => $userGift->itemId,
                        'status' => $userGift->status,
                        'fromUserName' => $userGift->fromUserName,
                        'senderUserName' => $userGift->senderUserName,
                        'userGiftImgUrl' => $userGiftImgUrl,
                        'isPrivate' => false,
                    ];
                }
            }
        }

        //fetch gift collection
        $giftCollection = $this->manageItemRepository->fetchListData(1);

        $giftListData = [];
        if (!__isEmpty($giftCollection)) {
            foreach ($giftCollection as $key => $giftData) {
                //only active gifts
                if ($giftData->status == 1) {
                    $giftImageUrl = '';
                    $giftImageFolderPath = getPathByKey('gift_image', ['{_uid}' => $giftData->_uid]);
                    $giftImageUrl = getMediaUrl($giftImageFolderPath, $giftData->file_name);
                    //get normal price or normal price is zero then show free gift
                    $normalPrice = (isset($giftData['normal_price']) and intval($giftData['normal_price']) <= 0) ? __tr('Free') : intval($giftData['normal_price']) . ' ' . __tr('credits');

                    //get premium price or premium price is zero then show free gift
                    $premiumPrice = (isset($giftData['premium_price']) and $giftData['premium_price'] <= 0) ? __tr('Free') : $giftData['premium_price'] . ' ' . __tr('credits');
                    $giftData['premium_price'] . ' ' . __tr('credits');

                    $price = __tr('Free');
                    //check user is premium or normal or Set price
                    if ($loggedInUserIsPremium) {
                        $price = $premiumPrice;
                    } else {
                        $price = $normalPrice;
                    }
                    $giftListData[] = [
                        '_id' => $giftData['_id'],
                        '_uid' => $giftData['_uid'],
                        'normal_price' => $normalPrice,
                        'premium_price' => $giftData['premium_price'],
                        'formattedPrice' => $price,
                        'gift_image_url' => $giftImageUrl,
                    ];
                }
            }
        }

        $specificationCollection = $this->userSettingRepository->fetchUserSpecificationById($userId);
        // Check if user specifications exists
        if (!\__isEmpty($specificationCollection)) {
            $userSpecifications = $specificationCollection->pluck('specification_value', 'specification_key')->toArray();
        }
        $specificationConfig = $this->getUserSpecificationConfig();
        foreach ($specificationConfig['groups'] as $specKey => $specification) {
            $items = [];
            if (!isset($specification['items'])) {
                $specification['items'] = [];
            }
            if (isset($specification['status']) and $specification['status'] == 0) {
                continue;
            }
            foreach ($specification['items'] as $itemKey => $item) {
                $itemValue = '';
                $userSpecValue = isset($userSpecifications[$itemKey])
                    ? $userSpecifications[$itemKey]
                    : '';
                if (!__isEmpty($userSpecValue)) {
                    $itemValue = isset($item['options'])
                        ? (isset($item['options'][$userSpecValue])
                            ? $item['options'][$userSpecValue] : '')
                        : $userSpecValue;
                }
                $items[] = [
                    'label' => $item['name'],
                    'value' => $itemValue,
                ];
            }

            // Check if Item exists
            if (!__isEmpty($items)) {
                $userSpecificationData[$specKey] = [
                    'title' => $specification['title'],
                    'items' => $items,
                ];
            }
        }

        //fetch block me users
        $blockMeUser = $this->userRepository->fetchBlockMeUser($user->_id);
        $isBlockUser = false;
        //check if not empty then set variable is true
        if (!__isEmpty($blockMeUser)) {
            $isBlockUser = true;
        }

        //fetch block by me user
        $blockUserData = $this->userRepository->fetchBlockUser($user->_id);
        $blockByMe = false;
        //if it is empty
        if (!__isEmpty($blockUserData)) {
            $blockByMe = true;
        }

        //fetch like dislike data by to user id
        $likeDislikeData = $this->userRepository->fetchLikeDislike($user->_id);

        $userLikeData = [];
        //check is not empty
        if (!__isEmpty($likeDislikeData)) {
            $userLikeData = [
                '_id' => $likeDislikeData->_id,
                'like' => $likeDislikeData->like,
            ];
        }

        //fetch user liked data by to user id
        $peopleILikeUserIds = $this->userRepository->fetchMyLikeDataByUserId($user->_id)->pluck('to_users__id')->toArray();

        return $this->engineReaction(1, [
            'userData' => $userData,
            'userProfileData' => $userProfileData,
            'photosData' => $photosData,
            'totalUserLike' => fetchTotalUserLikedCount($userId),
            'totalUserCredits' => totalUserCredits(),
            'totalVisitors' => $visitorData->count(),
            'userGiftData' => $userGiftData,
            'isPremiumUser' => isPremiumUser($userId),
            'isOwnProfile' => $isOwnProfile,
            'specifications' => (array) $userSpecificationData,
            'isBlockUser' => $isBlockUser,
            'blockByMeUser' => $blockByMe,
            'giftListData' => $giftListData,
            'userLikeData' => $userLikeData,
        ]);
    }

    /**
     * Process reset password request.
     *
     * @param  array  $input
     * @param  string  $reminderToken
     * @return array
     *---------------------------------------------------------------- */
    public function changeEmailProcess($input)
    {
        $email = $input['current_email'];

        //fetch active user by email
        $user = $this->userRepository->fetchActiveUserByEmail($email);

        // Check if user record exist
        if (__isEmpty($user)) {
            return  $this->engineReaction(18, ['show_message' => true], __tr('Invalid Request.'));
        }

        // Check if user entered correct password or not
        if (!Hash::check($input['current_password'], $user->password)) {
            return $this->engineReaction(3, [], __tr('Authentication Failed. Please Check Your Password.'));
        }

        // Check if user password updated
        if ($this->userRepository->updateUser($user, ['email' => $input['new_email']])) {
            return  $this->engineReaction(1, [
                'show_message' => true,
            ], __tr('Email updated successfully.'));
        }

        //failed response
        return  $this->engineReaction(2, ['show_message' => true], __tr('Email not updated.'));
    }

    /**
     * prepare profile details
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareProfileUpdate()
    {
        $user = $this->userRepository->fetchByID(getUserID());

        // Check if user exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }

        $userId = $user->_id;
        $userUid = $user->_uid;

        $basicInformation = $userSpecifications = $userSpecificationData = $locationInformation = [];

        // fetch User details
        $userProfile = $this->userSettingRepository->fetchUserProfile($userId);

        $profilePictureUrl = noThumbImageURL();
        $coverPictureUrl = noThumbCoverImageURL();
        $userSettingConfig = configItem('user_settings');
        $profilePictureFolderPath = getPathByKey('profile_photo', ['{_uid}' => $userUid]);
        $coverPictureFolderPath = getPathByKey('cover_photo', ['{_uid}' => $userUid]);

        // Check if user profile exists
        if (!__isEmpty($userProfile)) {
            if (!__isEmpty($userProfile->profile_picture)) {
                $profilePictureUrl = getMediaUrl($profilePictureFolderPath, $userProfile->profile_picture);
            }
            if (!__isEmpty($userProfile->cover_picture)) {
                $coverPictureUrl = getMediaUrl($coverPictureFolderPath, $userProfile->cover_picture);
            }
        }

        $dob = isset($userProfile['dob']) ? formatDate($userProfile['dob'], 'Y-m-d') : null;

        $explodedMobileNumber = explodeMobileNumber($user->mobile_number);

        // Prepare user data
        $basicInformation = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile_number' => $user->mobile_number,
            'mobile_number_without_country_code' => $explodedMobileNumber['mobile_number'],
            'country_code' => $explodedMobileNumber['country_code'],
            'work_status' => (string) isset($userProfile['work_status']) ? $userProfile['work_status'] : null,
            'gender' => (string) isset($userProfile['gender']) ? $userProfile['gender'] : null,
            'relationship_status' => (string) isset($userProfile['relationship_status']) ? $userProfile['relationship_status'] : null,
            'preferred_language' => (string) isset($userProfile['preferred_language']) ? $userProfile['preferred_language'] : null,
            'education' => (string) isset($userProfile['education']) ? $userProfile['education'] : null,
            'birthday' => $dob,
            'about_me' => isset($userProfile['about_me']) ? $userProfile['about_me'] : null,
            'country' => isset($userProfile['countries__id']) ? $userProfile['countries__id'] : null,
            'profile_picture' => $profilePictureUrl,
            'cover_picture' => $coverPictureUrl,
            'profileMediaRestriction' => getMediaRestriction('profile'),
            'coverImageMediaRestriction' => getMediaRestriction('cover_image'),
        ];

        // Prepare user data
        $locationInformation = [
            'country' => isset($userProfile['countries__id']) ? $userProfile['countries__id'] : null,
            'location_latitude' => isset($userProfile['location_latitude']) ? floatval($userProfile['location_latitude']) : null,
            'location_longitude' => isset($userProfile['location_longitude']) ? floatval($userProfile['location_longitude']) : null,
        ];

        $specificationCollection = $this->userSettingRepository->fetchUserSpecificationById($userId);

        // Check if user specifications exists
        if (!__isEmpty($specificationCollection)) {
            $userSpecifications = $specificationCollection->pluck('specification_value', 'specification_key')->toArray();
        }

        $specificationConfig = $this->getUserSpecificationConfig();

        foreach ($specificationConfig['groups'] as $specKey => $specification) {
            $items = [];

            foreach ($specification['items'] as $itemKey => $item) {
                $itemValue = '';
                $userSpecValue = isset($userSpecifications[$itemKey])
                    ? $userSpecifications[$itemKey]
                    : '';
                if (!__isEmpty($userSpecValue)) {
                    $itemValue = isset($item['options'])
                        ? (isset($item['options'][$userSpecValue])
                            ? $item['options'][$userSpecValue] : '')
                        : $userSpecValue;
                }

                $items[] = [
                    'name' => $itemKey,
                    'label' => $item['name'],
                    'value' => $itemValue,
                    'input_type' => $item['input_type'],
                    'options' => isset($item['options']) ? $item['options'] : '',
                    'selected_options' => $userSpecValue,
                ];
            }

            // Check if Item exists
            if (!__isEmpty($items)) {
                $userSpecificationData[$specKey] = [
                    'title' => $specification['title'],
                    'items' => $items,
                ];
            }
        }

        $allGenders = configItem('user_settings.gender');

        $genders = [];

        foreach ($allGenders as $key => $value) {
            $genders[] = [
                'id' => $key,
                'value' => $value,
            ];
        }

        return $this->engineReaction(1, [
            'basicInformation' => $basicInformation,
            'locationInformation' => $locationInformation,
            'specifications' => (array) $userSpecificationData,
            'countries' => $this->countryRepository->fetchAll()->toArray(),
            'user_settings' => configItem('user_settings'),
            'other_settings' => [
                'country_phone_codes' => getCountryPhoneCodes(),
                'min_age_year' => getAgeDate(configItem('age_restriction.maximum'), 'max')->format('Y-m-d'),
                'max_age_year' => getAgeDate(configItem('age_restriction.minimum'))->format('Y-m-d')
            ]
        ]);
    }

    /**
     * prepare featured users
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareFeaturedUsers()
    {
        $getFeatureUserList = getFeatureUserList();
        return $this->engineReaction(1, [
            'userRequestType' => 'featured_users',
            'getFeatureUserList' => $getFeatureUserList,
            'totalCount' => count($getFeatureUserList)
        ]);
    }


    /**
     * prepare user profile
     *
     * @return array
     *----------------------------------------------------------------*/
    public function userWelcomeNotifyMail($newUser)
    {
        $emailData = [
            'fullName' => $newUser->first_name,
            'email' => $newUser->email,
        ];

        $welcomeEmailSubject = "Welcome To " . getStoreSettings('name');

        if ($this->baseMailer->notifyToUser($welcomeEmailSubject, 'account.welcome', $emailData, $newUser->email)) {
            return true;
        }
    }
    /**
     * process To Send Otp
     *
     * @return json object
     *----------------------------------------------------------------*/
    public function processSendOtp($inputData)
    {

        if(getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($inputData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }

        $emailOrMobile = $inputData['emailOrMobile'];

        if (str_contains($emailOrMobile, '@')) {
            return $this->sendOtpEmail($inputData);
        } else {
            if (getStoreSettings('use_enable_sms_settings') == false) {
                return $this->engineReaction(2, ['show_message' => true], __tr('Mobile OTP is not available.'));
            }
        }

        if (!$this->userRepository->fetchActiveUserByEmailOrUsernameOrNumber($emailOrMobile)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Please use valid user credentials.'));
        }

        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);

        // Send the OTP to the user's phone number
        $message = __tr('Your OTP for __siteName__ is __loginOtp__', [
            '__siteName__' => getStoreSettings('name'),
            '__loginOtp__' => $otp
        ]); // Replace with your actual OTP message
        $explodeNumber = explode('-', $emailOrMobile);
        $countryCode = str_replace('0', '+', $explodeNumber[0]);
        $phone_numbers = str_replace($explodeNumber[0] . '-', $countryCode, $emailOrMobile);

        try {
            $this->smsProviders();
            Sms::via(getStoreSettings('sms_driver'))->send($message, function ($sms) use ($phone_numbers) {
                $sms->to($phone_numbers); # The numbers to send to.
            });

            // Store the otp in the session for later validation
            Session::put(['sms_otp' => $otp, 'emailOrMobile' => $inputData['emailOrMobile']]);

            //Send response back to controller
            return $this->engineReaction(1, ['show_message' => true], __tr('OTP has been sent successfully.'));
        } catch (\Throwable $th) {
            return $this->engineReaction(2, ['show_message' => true], $th->getMessage());
        }
    }

    /**
     * Verify Otp Process
     *
     * @param   array  $inputData
     *
     * @return  json object
     */
    public function verifyOtpProcess($inputData)
    {
        if(getStoreSettings('allow_recaptcha') and !$this->checkRecaptcha($inputData)) {
            return $this->engineReaction(2, ['show_message' => true], __tr('Invalid Re-captcha.'));
        }

        if (Session::has('sms_otp')) {
            $verification = Session::get('sms_otp');
            $emailOrMobile = Session::get('emailOrMobile');

            if ($verification != $inputData['otp']) {
                return $this->engineReaction(2, ['show_message' => true], __tr('Invalid OTP.'));
            }

            // OTP is valid, handle the successful verification
            if ($verification == $inputData['otp']) {

                Session::forget('sms_otp');

                $user = $this->userRepository->fetchByEmailOrUsername($emailOrMobile);

                //check user is empty
                if (__isEmpty($user)) {
                    return $this->engineReaction(2, null, __tr('User not exists.'));
                }

                //if user not active then show message
                if ($user->status != 1) {
                    return $this->engineReaction(2, null, __tr('Your account currently __status__, Please contact to administrator.', ['__status__' => configItem('status_codes', $user->status)]));
                }

                // Get logged in if credentials valid
                if (Auth::loginUsingId($user->_id)) {
                    //success response
                    return $this->engineReaction(1, [
                        'show_message' => true,
                        'auth_info' => getUserAuthInfo(1),
                    ], __tr('Welcome, you are logged in successfully.'));
                }
            }
            return $this->engineReaction(1, ['show_message' => true], __tr('Verification fail.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong.'));
    }

    /**
     * Send Otp to Email
     *
     * @param   array  $inputData
     *
     * @return  json object
     */
    public function sendOtpEmail($inputData)
    {
        $transactionResponse = $this->userRepository->processTransaction(function () use ($inputData) {
            $otp = rand(100000, 999999);
            $email = $inputData['emailOrMobile'];

            $emailData = [
                'email' => $email,
                'otp' => $otp
            ];

            // check if email send to member
            if ($this->baseMailer->notifyToUser('Your OTP Login Code for ' . getStoreSettings('name') . ' .', 'account.login-with-otp', $emailData, $inputData['emailOrMobile'])) {

                // Store the otp in the session for later validation
                Session::put(['sms_otp' => $otp, 'emailOrMobile' => $email]);

                return $this->userRepository->transactionResponse(1, [
                    'show_message' => true,
                ], __tr('OTP Send to Your Registered Email Address.'));
            }

            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Failed to send activation mail'));
        });

        return $this->engineReaction($transactionResponse);
    }


    /**
     * SMS Providers Settings
     *
     * @return
     */
    public function smsProviders()
    {
        return config([
            'sms.drivers.twilio.sid' => getStoreSettings('sms_twilio_sid'),
            'sms.drivers.twilio.token' => getStoreSettings('sms_twilio_token'),
            'sms.drivers.twilio.from' => getStoreSettings('sms_twilio_from'),

            'sms.drivers.textlocal.from' => getStoreSettings('sms_textlocal_from'),
            'sms.drivers.textlocal.username' => getStoreSettings('sms_textlocal_username'),
            'sms.drivers.textlocal.hash' => getStoreSettings('sms_textlocal_hash'),

            'sms.drivers.sms77.apiKey' => getStoreSettings('sms_sms77_apiKey'),
            'sms.drivers.sms77.flash' => getStoreSettings('sms_sms77_flash'),
            'sms.drivers.sms77.from' => getStoreSettings('sms_sms77_from'),
        ]);
    }
}
