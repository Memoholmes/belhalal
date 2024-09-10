<?php

/**
 * ManageUserEngine.php - Main component file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User;

use Carbon\Carbon;
use Faker\Generator as Faker;
use App\Yantrana\Base\BaseEngine;
use Illuminate\Support\Facades\Http;
use App\Yantrana\Support\CommonTrait;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Support\Country\Repositories\CountryRepository;
use App\Yantrana\Components\User\Repositories\ManageUserRepository;
use App\Yantrana\Components\User\Repositories\CreditWalletRepository;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use PushBroadcast;

class ManageUserEngine extends BaseEngine
{
    /**
     * @var CommonTrait - Common Trait
     */
    use CommonTrait;

    /**
     * @var  ManageUserRepository - ManageUser Repository
     */
    protected $manageUserRepository;

    /**
     * @var  CountryRepository - Country Repository
     */
    protected $countryRepository;

    /**
     * @var  Faker - Faker
     */
    protected $faker;

    /**
     * @var  CreditWalletRepository - CreditWallet Repository
     */
    protected $creditWalletRepository;

    /**
     * @var  MediaEngine - MediaEngine
     */
    protected $mediaEngine;

    /**
     * @var  UserRepository - UserRepository
     */
    protected $userRepository;

    /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * Constructor
     *
     * @param  ManageUserRepository  $manageUserRepository - ManageUser Repository
     * @param  CountryRepository  $countryRepository - Country Repository
     * @param  Faker  $faker - Faker
     * @param  MediaEngine  $mediaEngine - MediaEngine
     * @param  CreditWalletRepository  $creditWalletRepository - CreditWallet Repository
     * @param  UserRepository  $userRepository - User Repository
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(ManageUserRepository $manageUserRepository, CountryRepository $countryRepository, Faker $faker, CreditWalletRepository $creditWalletRepository, MediaEngine $mediaEngine, UserRepository $userRepository, CreditPackageRepository $creditPackageRepository)
    {
        $this->manageUserRepository = $manageUserRepository;
        $this->countryRepository = $countryRepository;
        $this->faker = $faker;
        $this->creditWalletRepository = $creditWalletRepository;
        $this->mediaEngine = $mediaEngine;
        $this->userRepository = $userRepository;
        $this->creditPackageRepository = $creditPackageRepository;
    }

    /**
     * Prepare User Data table list.
     *
     * @param  int  $status
     *
     *---------------------------------------------------------------- */
    public function prepareUsersDataTableList($status, $userType = null)
    {
        $userCollection = $this->manageUserRepository->fetchUsersDataTableSource($status, $userType);
      
        $requireColumns = [
            '_id',
            '_uid',
            'first_name',
            'last_name',
            'full_name',
            'created_at' => function ($key) {
                return formatDate($key['created_at']);
            },
            'status' =>function ($key) {
                return configItem('status_codes', $key['status']);
            },
            'email',
            'username',
            'is_fake',
            'is_verified' => function ($key) {
                if (isset($key['is_verified']) and $key['is_verified'] == 1) {
                    return true;
                }

                return false;
            },
            'is_premium_user' => function ($key) {
                return isPremiumUser($key['_id']);
            },
            'dob' => function ($key) {
                //check is not empty
                if (! __isEmpty($key['dob'])) {
                    return $key['dob'];
                }

                return '-';
            },
            'gender',
            'formattedGender' => function ($key) {
                //check is not empty
                if (! __isEmpty($key['gender'])) {
                    return configItem('user_settings.gender', $key['gender']);
                }

                return '-';
            },
            'registered_via' => function ($key) {
                if (! __isEmpty($key['registered_via'])) {
                    return $key['registered_via'];
                }

                return '-';
            },
            'profile_picture' => function ($key) {
                if (isset($key['profile_picture']) and ! __isEmpty($key['profile_picture'])) {
                    $imagePath = getPathByKey('profile_photo', ['{_uid}' => $key['_uid']]);

                    return getMediaUrl($imagePath, $key['profile_picture']);
                }

                return noThumbImageURL();
            },
            'profile_url' => function ($key) {
                return route('user.profile_view', ['username' => $key['username']]);
            },
            'user_roles__id',
            'mobile_number' => function ($key) {
                $withoutCodeMblNo = explode('-', ($key['mobile_number']));
                $mobileNumber = isset($withoutCodeMblNo[1]) ? $withoutCodeMblNo[1] : '';
                return $mobileNumber;
            },
            'country_code' => function ($key) {
                $withoutCodeMblNo = explode('-', ($key['mobile_number']));
                $countryCode = explode('0', $withoutCodeMblNo[0]);
                $countryPhnCode = isset($countryCode[1]) ? ($countryCode[1]) : '';
                return $countryPhnCode;

            },
            'preview_url' => function ($key) {
                return route('manage.user.write.update', [
                    'userUid' => $key['_uid'],
                ]);
            },
            'status_code' => function ($key) {
                return $key['status'];
            },
        ];

        return $this->dataTableResponse($userCollection, $requireColumns);
    }

    /**
     * Prepare User photos Data table list.
     *
     * @param  int  $status
     *
     *---------------------------------------------------------------- */
    public function userPhotosDataTableList()
    {
        $userCollection = $this->manageUserRepository->fetchUserPhotos();

        $requireColumns = [
            '_id',
            '_uid',
            'first_name',
            'last_name',
            'full_name',
            'profile_image' => function ($key) {
                if (isset($key['image_name'])) {
                    $path = getPathByKey('user_photos', ['{_uid}' => $key['_uid']]);

                    return getMediaUrl($path, $key['image_name']);
                } elseif (isset($key['profile_picture'])) {
                    $path = getPathByKey('profile_photo', ['{_uid}' => $key['_uid']]);

                    return getMediaUrl($path, $key['profile_picture']);
                } elseif (isset($key['cover_picture'])) {
                    $path = getPathByKey('cover_photo', ['{_uid}' => $key['_uid']]);

                    return getMediaUrl($path, $key['cover_picture']);
                }

                return null;
            },
            'updated_at' => function ($key) {
                return formatDate($key['updated_at'], 'l jS F Y g:i A');
            },
            'type' => function ($key) {
                if (isset($key['image_name'])) {
                    return 'photo';
                } elseif (isset($key['profile_picture'])) {
                    return 'profile';
                } elseif (isset($key['cover_picture'])) {
                    return 'cover';
                }

                return null;
            },
            'profile_url' => function ($key) {
                return route('user.profile_view', ['username' => $key['username']]);
            },
            'deleteImageUrl' => function ($key) {
                if (isset($key['image_name'])) {
                    return route('manage.user.write.photo_delete', [
                        'userUid' => $key['_uid'],
                        'type' => 'photo',
                        'profileOrPhotoUid' => $key['user_photo_id'],
                    ]);
                } elseif (isset($key['profile_picture'])) {
                    return route('manage.user.write.photo_delete', [
                        'userUid' => $key['_uid'],
                        'type' => 'profile',
                        'profileOrPhotoUid' => $key['user_profile_id'],
                    ]);
                } elseif (isset($key['cover_picture'])) {
                    return route('manage.user.write.photo_delete', [
                        'userUid' => $key['_uid'],
                        'type' => 'cover',
                        'profileOrPhotoUid' => $key['user_profile_id'],
                    ]);
                }
            },
        ];

        return $this->dataTableResponse($userCollection, $requireColumns);
    }

    /**
     * Prepare User List.
     *
     * @param  int  $status
     *
     *---------------------------------------------------------------- */
    public function prepareUserList($status)
    {
        $userCollection = $this->manageUserRepository->fetchList($status);
        $userData = [];
        // check if user collection exists
        if (! __isEmpty($userCollection)) {
            foreach ($userCollection as $user) {
                $userData[] = [
                    'uid' => $user->_uid,
                    'full_name' => $user->first_name.' '.$user->last_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'created_on' => formatDate($user->created_at),
                ];
            }
        }

        return $this->engineReaction(1, [
            'userData' => $userData,
        ]);
    }

    /**
     * Prepare User List.
     *
     * @param  array  $inputData
     *
     *---------------------------------------------------------------- */
    public function processAddUser($inputData)
    {
        $transactionResponse = $this->manageUserRepository->processTransaction(function () use ($inputData) {
            // Store user
            $newUser = $this->manageUserRepository->storeUser($inputData);
            // Check if user not stored successfully
            if (! $newUser) {
                return $this->manageUserRepository->transactionResponse(2, ['show_message' => true], __tr('User not added.'));
            }
            $userAuthorityData = [
                'user_id' => $newUser->_id,
                'user_roles__id' => 2,
            ];
            // Add user authority
            if ($this->manageUserRepository->storeUserAuthority($userAuthorityData)) {
                return $this->manageUserRepository->transactionResponse(1, ['show_message' => true], __tr('User added successfully.'));
            }
            // Send failed server error message
            return $this->manageUserRepository->transactionResponse(2, ['show_message' => true], __tr('Something went wrong on server.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Prepare User edit data.
     *
     * @param  array  $userUid
     *
     *---------------------------------------------------------------- */
    public function prepareUserEditData($userUid)
    {
       
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        $withoutCodeMblNo = explode('-', $userDetails->mobile_number);
        $mobileNumber = isset($withoutCodeMblNo[1]) ? $withoutCodeMblNo[1] : '';
        $countryCode = explode('0',$withoutCodeMblNo[0]);
        $countryPhnCode = isset($countryCode[1]) ? ($countryCode[1]) : '';

        $userData = [
            'uid' => $userDetails->_uid,
            'first_name' => $userDetails->first_name,
            'last_name' => $userDetails->last_name,
            'email' => $userDetails->email,
            'username' => $userDetails->username,
            'password' => $userDetails->password,
            'confirm_password' => $userDetails->confirm_password,
            'designation' => $userDetails->designation,
            'mobile_number' => $mobileNumber,
            'status' => $userDetails->status,
            'country_code' => $countryPhnCode,
        ];

        return $this->engineReaction(1, [
            'userData' => $userData,
        ]);
    }

    /**
     * Process User Update.
     *
     * @param  string  $userUid
     * @param  array  $inputData
     *
     *---------------------------------------------------------------- */
    public function processUserUpdate($userUid, $inputData)
    { 
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        $mobileNumber = '0'. $inputData['country_code']. '-'. $inputData['mobile_number'];
        $status = 2;
        if($inputData['userStatus'] == 4 || $inputData['userStatus'] == 3 || $inputData['userStatus'] == 5)
        {
             $status = $inputData['userStatus'];
        }
        // Prepare Update User data
        $updateData = [
            'first_name' => $inputData['first_name'],
            'last_name' => $inputData['last_name'],
            'email' => $inputData['email'],
            'username' => $inputData['username'],
            'designation' => array_get($inputData, 'designation'),
            'mobile_number' => $mobileNumber,
            'status' => (isset($inputData['status'])
        and $inputData['status'] == 'on') ? 1 : $status,
        ];
        // check if user updated
        if ($this->manageUserRepository->updateUser($userDetails, $updateData)) {
            // Adding activity log for update user
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user info updated.');

            return $this->engineReaction(1, ['show_message' => true], __tr('User updated successfully.'));
        }

        return $this->engineReaction(14, ['show_message' => true], __tr('Nothing updated.'));
    }

    /**
     * Process Soft Delete User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processSoftDeleteUser($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        // Prepare Update User data
        $updateData = [
            'status' => 5,
        ];

        // check if user soft deleted
        if ($this->manageUserRepository->updateUser($userDetails, $updateData)) {
            // Add activity log for user soft deleted
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user soft deleted.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid, 'show_message' => true], __tr('User soft deleted successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Process Soft Delete User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processPermanentDeleteUser($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        // check if user soft deleted first
        if ($userDetails->status != 5) {
            return $this->engineReaction(2, ['show_message' => true], __tr('To delete user permanently you have to soft delete first.'));
        }
        // check if user deleted
        if ($this->manageUserRepository->deleteUser($userDetails)) {
            // Add activity log for user permanent deleted
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user permanent deleted.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid, 'show_message' => true], __tr('User permanent deleted successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Process Restore User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processUserRestore($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        // Prepare Update User data
        $updateData = [
            'status' => 1,
        ];

        // check if restore deleted
        if ($this->manageUserRepository->updateUser($userDetails, $updateData)) {
            // Add activity log for user restored
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user restored.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid, 'show_message' => true], __tr('User restore successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Process Block User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processBlockUser($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        // Prepare Update User data
        $updateData = [
            'status' => 3, // Blocked
        ];

        // check if user blocked
        if ($this->manageUserRepository->updateUser($userDetails, $updateData)) {
            // Add activity log for user blocked
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user blocked.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid, 'show_message' => true], __tr('User blocked successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Process Unblock User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processUnblockUser($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }
        // Prepare Update User data
        $updateData = [
            'status' => 1, // Active
        ];

        // check if user soft deleted
        if ($this->manageUserRepository->updateUser($userDetails, $updateData)) {
            // Add activity log for user unblocked
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user unblocked.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid, 'show_message' => true], __tr('User unblocked successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Prepare User edit data.
     *
     * @param  array  $userUid
     *
     *---------------------------------------------------------------- */
    public function prepareUserDetails($userUid)
    {
        $user = $this->manageUserRepository->fetchUser($userUid);
        // check if user details exists
        if (__isEmpty($user)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }

        $userDetails = [
            'full_name' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
            'username' => $user->username,
            'designation' => $user->designation,
            'mobile_number' => $user->mobile_number,
        ];

        return $this->engineReaction(1, [
            'userDetails' => $userDetails,
        ]);
    }

    /**
     * prepare Fake User Generator Options.
     *
     *---------------------------------------------------------------- */
    public function prepareFakeUserOptions()
    {
        //user options
        $userSettings = configItem('user_settings');
        $fakerGeneratorOptions = configItem('fake_data_generator');

        //countries
        $countries = $this->countryRepository->fetchAll();
        $countryIds = $countries->pluck('id')->toArray();

        return $this->engineReaction(1, [
            'gender' => $userSettings['gender'],
            'languages' => $userSettings['preferred_language'],
            'default_password' => $fakerGeneratorOptions['default_password'],
            'recordsLimit' => $fakerGeneratorOptions['records_limits'],
            'countries' => $countries->toArray(),
            'randomData' => [
                'country' => array_rand($countryIds),
                'gender' => array_rand(($userSettings['gender'])),
                'language' => array_rand(($userSettings['preferred_language'])),
            ],
            'ageRestriction' => configItem('age_restriction'),
        ]);
    }

    /**
     * prepare Fake User Generator Options.
     *
     *---------------------------------------------------------------- */
    public function processGenerateFakeUser($options)
    {
        $transactionResponse = $this->manageUserRepository->processTransaction(function () use ($options) {
            $countries = $this->countryRepository->fetchAll()->pluck('id')->toArray();

            //for page number
            if (__isEmpty(session('fake_user_api_page_no')) or (session('fake_user_api_page_no') >= 9)) {
                session(['fake_user_api_page_no' => 1]);
            } else {
                $page = session('fake_user_api_page_no');
                session(['fake_user_api_page_no' => $page + 1]);
            }

            $page = session('fake_user_api_page_no');

            //get All photo ids
            $photoIds = collect(getPhotosFromAPI($page))->pluck('id')->toArray();
            //user options
            $userSettings = configItem('user_settings');

            $specificationConfig = $this->getUserSpecificationConfig();

            $usersAdded = $authoritiesAdded = $profilesAdded = $creditWallets = $specsAdded = false;
            $users = [];
            $creditWalletStoreData = [];

            $randomApi = Http::get('https://randomuser.me/api/?', [
                'results' => $options['number_of_users'],
            ]);
            $randomUser = json_decode($randomApi->getBody()->getContents(),true)['results'];

            //for users
            for ($i = 0; $i < $options['number_of_users']; $i++) {
                //random timezone
                $timezone = $randomUser[$i]['location']['timezone']['description'];
                $createdDate = Carbon::now()->addMinutes($i + 1);

                $users[] = [
                    'first_name' => $randomUser[$i]['name']['first'],
                    'last_name' => $randomUser[$i]['name']['last'],
                    'email' => $randomUser[$i]['email'],
                    'username' => $randomUser[$i]['login']['username'],
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                    'password' => bcrypt($options['default_password']),
                    'status' => 1,
                    'mobile_number' => $randomUser[$i]['phone'],
                    'timezone' => $timezone,
                    'is_fake' => 1,
                ];
                unset($createdDate);
            }

            // Store users
            $addedUsersIds = $this->manageUserRepository->storeMultipleUsers($users);

            //check if users added
            if ($addedUsersIds) {
                $usersAdded = true;
                $authorities = $profiles = $specifications = [];
                // for authority
                foreach ($addedUsersIds as $key => $addedUserID) {
                    $createdDate = Carbon::now()->addMinutes($key + 1);
                    //authorities
                    $authorities[] = [
                        'created_at' => $createdDate,
                        'updated_at' => $createdDate,
                        'status' => 1,
                        'users__id' => $addedUserID,
                        'user_roles__id' => 2,
                    ];

                    //random age
                    $age = rand($options['age_from'], $options['age_to']);

                    $country = $options['country'];

                    //check if country is random or not set
                    if ($options['country'] == 'random' or __isEmpty($options['country'])) {
                        $randomKey = array_rand($countries);
                        $country = $countries[$randomKey];
                    }

                    //check if gender is random or not set
                    $gender = $options['gender'];
                    if ($options['gender'] == 'random' or __isEmpty($options['gender'])) {
                        $gender = array_rand($userSettings['gender']);
                    }

                    //check if language is random or not set
                    $language = $options['language'];
                    if ($options['language'] == 'random' or __isEmpty($options['language'])) {
                        $language = array_rand($userSettings['preferred_language']);
                    }

                    //for profiles
                    $profiles[] = [
                        'created_at' => $createdDate,
                        'updated_at' => $createdDate,
                        'users__id' => $addedUserID,
                        'countries__id' => $country,
                        'gender' => $gender,
                        'profile_picture' => strtr('https://picsum.photos/id/__imageID__/360/360', ['__imageID__' => array_rand($photoIds)]),
                        'cover_picture' => strtr('https://picsum.photos/id/__imageID__/820/360', ['__imageID__' => array_rand($photoIds)]),
                        'dob' => Carbon::now()->subYears($age)->format('Y-m-d'),
                        'city' => $this->faker->city,
                        'about_me' => $this->faker->text(rand(50, 500)),
                        'work_status' => array_rand($userSettings['work_status']),
                        'education' => array_rand($userSettings['educations']),
                        'is_verified' => rand(0, 1),
                        'location_latitude' => $this->faker->latitude,
                        'location_longitude' => $this->faker->longitude,
                        'preferred_language' => $language,
                        'relationship_status' => array_rand($userSettings['relationship_status']),
                    ];
                    unset($createdDate);
                    //check enable bonus credits for new user
                    if (getStoreSettings('enable_bonus_credits')) {
                        $creditWalletStoreData[] = [
                            'status' => 1,
                            'users__id' => $addedUserID,
                            'credits' => getStoreSettings('number_of_credits'),
                            'credit_type' => 1, //Bonuses
                        ];
                    }

                    if (! __isEmpty($specificationConfig['groups'])) {
                        foreach ($specificationConfig['groups'] as $key => $group) {
                            if (in_array($key, ['looks', 'personality', 'lifestyle'])) {
                                if (! __isEmpty($group['items'])) {
                                    foreach ($group['items'] as $key2 => $item) {
                                        $specifications[] = [
                                            'type' => 1,
                                            'status' => 1,
                                            'specification_key' => $key2,
                                            'specification_value' => isset($item['options']) ? array_rand($item['options']) : null,
                                            'users__id' => $addedUserID,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                //check if authorities added
                if ($this->manageUserRepository->storeUserAuthorities($authorities)) {
                    $authoritiesAdded = true;
                }

                //check if profiles added
                if ($this->manageUserRepository->storeUserProfiles($profiles)) {
                    $profilesAdded = true;
                }

                //check if profiles added
                if (! __isEmpty($specifications)) {
                    $this->manageUserRepository->storeUserSpecifications($specifications);
                }

                if (! __isEmpty($creditWalletStoreData)) {
                    //store user credit transaction data
                    $this->manageUserRepository->storeCreditWalletTransactions($creditWalletStoreData);
                }
            }

            //if all data inserted
            if ($usersAdded and $authoritiesAdded and $profilesAdded) {
                return $this->manageUserRepository->transactionResponse(1, ['show_message' => true], __tr('Fake users added successfully.'));
            }

            // // Send failed server error message
            return $this->manageUserRepository->transactionResponse(2, ['show_message' => true], __tr('Fake users not added.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Process Block User.
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processVerifyUserProfile($userUid)
    {
        $userDetails = $this->manageUserRepository->fetchUser($userUid);

        // check if user details exists
        if (__isEmpty($userDetails)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('User does not exists.'));
        }

        $profileAddedAndVerified = $profileVerified = false;

        $profile = $this->manageUserRepository->fetchUserProfile($userDetails->_id);

        // check if profile is empty , if true then create profile
        if (__isEmpty($profile)) {
            if ($this->manageUserRepository->storeUserProfile(['users__id' => $userDetails->_id, 'is_verified' => 1])) {
                $profileAddedAndVerified = true;
            }
        } else {
            if ($this->manageUserRepository->updateUserProfile($profile, ['is_verified' => 1])) {
                $profileVerified = true;
            }
        }

        // check if user added and verified
        if ($profileAddedAndVerified or $profileVerified) {
            // Add activity log for user blocked
            activityLog($userDetails->first_name.' '.$userDetails->last_name.' user verified.');

            return $this->engineReaction(1, ['userUid' => $userDetails->_uid], __tr('User verified successfully.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * get manage  user transaction list data.
     *
     * @param $userUid
     * @return object
     *---------------------------------------------------------------- */
    public function getUserTransactionList($userUid)
    {
        $user = $this->manageUserRepository->fetchUser($userUid);
        //if user not exist
        if (__isEmpty($user)) {
            return $this->engineReaction(2, null, __tr('User does not exist.'));
        }

        $transactionCollection = $this->creditWalletRepository->fetchUserTransactionListData($user->_id);

        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($key) {
                return formatDate($key['created_at']);
            },
            'updated_at' => function ($key) {
                return formatDate($key['updated_at']);
            },
            'status',
            'formattedStatus' => function ($key) {
                return configItem('payments.status_codes', $key['status']);
            },
            'amount',
            'formattedAmount' => function ($key) {
                return priceFormat($key['amount'], true, true);
            },
            'method',
            'currency_code',
            'is_test',
            'formattedIsTestMode' => function ($key) {
                return configItem('payments.payment_checkout_modes', $key['is_test']);
            },
            'credit_type',
            'formattedCreditType' => function ($key) {
                return configItem('payments.credit_type', $key['credit_type']);
            },
            '__data',
            'packageName' => function ($key) {
                //check is not Empty
                if (! __isEmpty($key['__data']) and ! __isEmpty($key['__data']['packageName'])) {
                    return $key['__data']['packageName'];
                }

                return 'N/A';
            },
        ];

        return $this->dataTableResponse($transactionCollection, $requireColumns);
    }

    /**
     * Delete photo, cover or profile of user .
     *
     * @param  string  $userUid
     *
     *---------------------------------------------------------------- */
    public function processUserPhotoDelete($userUid, $type, $profileOrPhotoUid)
    {
        $transactionResponse = $this->manageUserRepository->processTransaction(function () use ($userUid, $type, $profileOrPhotoUid) {
            $userDetails = $this->manageUserRepository->fetchUser($userUid);

            // check if user details exists
            if (__isEmpty($userDetails)) {
                return $this->manageUserRepository->transactionResponse(18, null, __tr('User does not exists.'));
            }

            //if type is photo
            if ($type == 'photo') {
                $userPhoto = $this->manageUserRepository->getUsersPhoto($userDetails->_id, $profileOrPhotoUid);
                $imagePath = getPathByKey('user_photos', ['{_uid}' => $userDetails->_uid]);

                //if deleted
                if ($this->manageUserRepository->deleteUserPhoto($userPhoto)) {
                    $this->mediaEngine->processDeleteFile($imagePath, $userPhoto->file);

                    return $this->manageUserRepository->transactionResponse(1, ['show_message' => true], __tr('Photo removed successfully.'));
                }
            } elseif ($type == 'profile') {
                $profile = $this->manageUserRepository->fetchUserProfile($userDetails->_id);

                //check if url
                if (! isImageUrl($profile->profile_picture)) {
                    $imagePath = getPathByKey('profile_photo', ['{_uid}' => $userDetails->_uid]);
                    $this->mediaEngine->processDeleteFile($imagePath, $profile->profile_picture);
                }
                // Add activity log for user soft deleted
                $this->manageUserRepository->updateUserProfile($profile, ['profile_picture' => null]);
                activityLog($userDetails->first_name.' '.$userDetails->last_name.' user profile photo deleted.');

                return $this->manageUserRepository->transactionResponse(1, ['show_message' => true], __tr('Photo removed successfully.'));
                // }
            } elseif ($type == 'cover') {
                $profile = $this->manageUserRepository->fetchUserProfile($userDetails->_id);

                //check if url
                if (! isImageUrl($profile->profile_picture)) {
                    $imagePath = getPathByKey('cover_photo', ['{_uid}' => $userDetails->_uid]);
                    $this->mediaEngine->processDeleteFile($imagePath, $profile->cover_picture);
                }
                // Add activity log for user soft deleted
                $this->manageUserRepository->updateUserProfile($profile, ['cover_picture' => null]);
                activityLog($userDetails->first_name.' '.$userDetails->last_name.' user cover photo soft deleted.');

                return $this->manageUserRepository->transactionResponse(1, ['show_message' => true], __tr('Photo removed successfully.'));
            }

            return $this->manageUserRepository->transactionResponse(2, ['show_message' => true], __tr('Something went wrong on server.'));
        });

        return $this->engineReaction($transactionResponse);
    }

    /**
     * Add Allocated Credits For User
     *
     * @param  array  $inputData
     * @return  json object
     */
    public function allocatedCreditsForUser($inputData)
    {
        $transactionResponse = $this->creditWalletRepository->processTransaction(function () use ($inputData) {
            $userDetails = $this->userRepository->fetchByUid($inputData['userId']);

            $storeCreditWalletTransaction = [
                'status' => 1,
                'users__id' => $userDetails->_id,
                'credits' => $inputData['allocate_credits'],
                'credit_type' => 1, // allocate bonus credits
                'description' => 'admin_credit'
            ];

            if ($this->creditWalletRepository->storeWalletTransaction($storeCreditWalletTransaction)) {
                //Send Notification to user
                PushBroadcast::notifyViaPusher('event.user.credit', [
                    'type' => 'credit-allowed',
                    'userUid' => $userDetails->_uid,
                    'subject' => __tr('Credits Allowed successfully'),
                    'message' => $inputData['allocate_credits'] . __tr(' credits are allowed to you'),
                    'credits' => $inputData['allocate_credits'],
                    'messageType' => __tr('success')
                ]);

                return $this->creditWalletRepository->transactionResponse(1, [
                    'show_message' => true,
                ], __tr('Credits store successfully.'));
            }

            return $this->creditWalletRepository->transactionResponse(2, ['show_message' => true], __tr('Fail to store credits.'));
        });

        return $this->engineReaction($transactionResponse);
    }
}
