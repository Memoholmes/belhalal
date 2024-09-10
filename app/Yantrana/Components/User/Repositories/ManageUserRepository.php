<?php
/**
* ManageUserRepository.php - Repository file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\User\Models\CreditWalletTransaction;
use App\Yantrana\Components\User\Models\Faker\FakerUserAuthority;
use App\Yantrana\Components\User\Models\Faker\FakerUserModel;
use App\Yantrana\Components\User\Models\Faker\FakerUserProfile;
use App\Yantrana\Components\User\Models\User;
use App\Yantrana\Components\User\Models\UserAuthorityModel;
use App\Yantrana\Components\User\Models\UserProfile;
use App\Yantrana\Components\UserSetting\Models\UserPhotosModel;
use App\Yantrana\Components\UserSetting\Models\UserSpecificationModel;
use DB;

class ManageUserRepository extends BaseRepository
{
    /**
     * Fetch User
     *
     * @param    int || string $status
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUser($userId)
    {
        if (is_numeric($userId)) {
            return User::where('_id', $userId)->first();
        }

        return User::where('_uid', $userId)->first();
    }

    /**
     * Fetch List of users
     *
     * @param    int || int $status
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUsersDataTableSource($status, $userType = null)
    {
        $dataTableConfig = [
            'searchable' => [
                'first_name',
                'last_name',
                'mobile_number',
                'username',
                'email',
                'full_name' => DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"),
                'user_profiles.dob',
                'user_profiles.gender',
            ],
        ];
        $where = [
            'users.status' => $status,
            'users.is_fake' => null,
        ];

        if($userType == 'fake') {
            $where = [
                'users.is_fake' => 1,
            ];
        }

        return User::leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            ->leftJoin('user_authorities', 'users._id', '=', 'user_authorities.users__id')
            ->where($where)
            ->select(
                __nestedKeyValues([
                    'users' => [
                        '_id',
                        '_uid',
                        'first_name',
                        'last_name',
                        'created_at',
                        'status',
                        'email',
                        'username',
                        'is_fake',
                        'registered_via',
                        'mobile_number',
                        DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS full_name"),
                    ],
                    'user_profiles' => [
                        'is_verified',
                        'profile_picture',
                        'gender',
                        'dob',
                    ],
                    'user_authorities' => [
                        '_id AS user_authority_id',
                        'user_roles__id',
                    ],
                ])
            )
            ->dataTables($dataTableConfig)
            ->toArray();
    }

    /**
     * Fetch List of users
     *
     * @param    int || int $status
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchList($status)
    {
        return User::where('status', $status)->get();
    }

    /**
     * Store User.
     *
     * @param  array  $storeData
     *
     *-----------------------------------------------------------------------*/
    public function storeUser($storeData)
    {
        $keyValues = [
            'email',
            'password' => bcrypt($storeData['password']),
            'status' => array_get($storeData, 'status', 2),
            'first_name',
            'last_name',
            'username',
            'designation',
            'mobile_number',
        ];
        // Get Instance of user model
        $userModel = new User;
        // Store New User
        if ($userModel->assignInputsAndSave($storeData, $keyValues)) {
            activityLog($userModel->first_name.' '.$userModel->last_name.' user created.');

            return $userModel;
        }

        return false;
    }

    /**
     * Store User Authority.
     *
     * @param  array  $userAuthorityData
     *
     *-----------------------------------------------------------------------*/
    public function storeUserAuthority($userAuthorityData)
    {
        $keyValues = [
            'status' => 1,
            'users__id' => $userAuthorityData['user_id'],
            'user_roles__id' => $userAuthorityData['user_roles__id'],
        ];
        // Get Instance of user authority model
        $userAuthorityModel = new UserAuthorityModel;
        // Store New User Authority
        if ($userAuthorityModel->assignInputsAndSave($userAuthorityData, $keyValues)) {
            return $userAuthorityModel;
        }

        return false;
    }

    /**
     * Update User.
     *
     * @param  object  $user
     * @return bool
     *---------------------------------------------------------------- */
    public function updateUser($user, $updateData)
    {
        // Check if information updated
        if ($user->modelUpdate($updateData)) {
            return true;
        }

        return false;
    }

    /**
     * Delete User
     *
     * @param  object  $user
     * @return bool
     *---------------------------------------------------------------- */
    public function deleteUser($user)
    {
        // Check if information deleted
        if ($user->delete()) {
            activityLog($user->first_name.' '.$user->last_name.' user deleted.');

            return true;
        }

        return false;
    }

    /**
     * Store User.
     *
     * @param  array  $storeData
     *
     *-----------------------------------------------------------------------*/
    public function storeMultipleUsers($storeData)
    {
        // Get Instance of user model
        $userModel = new FakerUserModel;
        // Store New User
        if ($userIds = $userModel->prepareAndInsert($storeData, '_id')) {
            activityLog(strtr('__usersCount__ fake users created.', ['__usersCount__' => count($userIds)]));

            return $userIds;
        }

        return false;
    }

    /**
     * Store User Authorities.
     *
     * @param  array  $storeData
     *
     *-----------------------------------------------------------------------*/
    public function storeUserAuthorities($storeData)
    {
        // Get Instance of user model
        $authorityModel = new FakerUserAuthority;

        // Store New User
        if ($authorityIds = $authorityModel->prepareAndInsert($storeData, '_id')) {
            activityLog(strtr('__usersCount__ fake users authority created.', ['__usersCount__' => count($authorityIds)]));

            return $authorityIds;
        }

        return false;
    }

    /**
     * Store User Profiles.
     *
     * @param  array  $storeData
     *
     *-----------------------------------------------------------------------*/
    public function storeUserProfiles($storeData)
    {
        // Get Instance of user model
        $profileModel = new FakerUserProfile;

        // Store New User
        if ($profileIds = $profileModel->prepareAndInsert($storeData, '_id')) {
            activityLog(strtr('__usersCount__ fake users profiles created.', ['__usersCount__' => count($profileIds)]));

            return $profileIds;
        }

        return false;
    }

    /**
     * Store Credit Wallet transactions.
     *
     * @param  array  $storeData
     *
     *-----------------------------------------------------------------------*/
    public function storeCreditWalletTransactions($storeData)
    {
        // Get Instance of user model
        $creditWalletTransaction = new CreditWalletTransaction;

        // Store New User
        if ($walletIds = $creditWalletTransaction->prepareAndInsert($storeData, '_id')) {
            activityLog(strtr('__usersCount__ fake users credit wallet added.', ['__usersCount__' => count($walletIds)]));

            return $walletIds;
        }

        return false;
    }

    /**
     * Store or update user specification data
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeUserSpecifications($inputData)
    {
        $userSpecModel = new UserSpecificationModel();

        // Check if data updated or inserted
        if ($userSpecModel->prepareAndInsert($inputData, '_id')) {
            return true;
        }

        return false;
    }

    /**
     * get User Profile.
     *
     * @param int userID
     *
     *-----------------------------------------------------------------------*/
    public function fetchUserProfile($userID)
    {
        return UserProfile::where('users__id', '=', $userID)->first();
    }

    /**
     * Store user profile
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeUserProfile($inputData)
    {
        $keyValues = [
            'users__id' => $inputData['users__id'],
            'countries__id' => array_get($inputData, 'countries__id'),
            'gender' => array_get($inputData, 'gender'),
            'dob' => array_get($inputData, 'dob'),
            'about_me' => array_get($inputData, 'about_me'),
            'city' => array_get($inputData, 'city'),
            'work_status' => array_get($inputData, 'work_status'),
            'education' => array_get($inputData, 'education'),
            'preferred_language' => array_get($inputData, 'preferred_language'),
            'relationship_status' => array_get($inputData, 'relationship_status'),
            'location_latitude' => array_get($inputData, 'location_latitude'),
            'location_longitude' => array_get($inputData, 'location_longitude'),
            'is_verified' => array_get($inputData, 'is_verified'),
        ];

        $userProfile = new UserProfile;
        // check if user profile stored successfully
        if ($userProfile->assignInputsAndSave($inputData, $keyValues)) {
            return $userProfile;
        }

        return false;
    }

    /**
     * Update User Profile
     *
     * @param  object  $userProfile
     * @param  array  $updateData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function updateUserProfile($userProfile, $updateData)
    {
        if ($userProfile->modelUpdate($updateData)) {
            return true;
        }

        return false;
    }

    /**
     * fetch user photos
     *
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserPhotos()
    {
        $request = request()->all();

        $searchable = [
            'first_name',
            'last_name',
            'full_name' => DB::raw("CONCAT(first_name, ' ', last_name)"),
        ];

        $sortBy = 'updated_at';

        $search = (isset($request['search']) and ! __isEmpty($request['search']['value'])) ? $request['search']['value'] : null;

        $userPhotos = User::leftJoin('user_photos', 'users._id', '=', 'user_photos.users__id')
            ->whereNotNull('user_photos._uid')
            ->select(
                __nestedKeyValues([
                    'users' => [
                        '_id',
                        '_uid',
                        'first_name',
                        'last_name',
                        'username',
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS full_name"),
                    ],
                    'user_photos' => [
                        '_uid as user_photo_id',
                        'file as image_name',
                        'updated_at',
                    ],
                ])
            )
            ->shodh($search, $searchable)
            ->get()
            ->toArray();

        $userProfilePhotos = User::leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            ->whereNotNull('user_profiles.profile_picture')
            ->select(
                __nestedKeyValues([
                    'users' => [
                        '_id',
                        '_uid',
                        'first_name',
                        'last_name',
                        'username',
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS full_name"),
                    ],
                    'user_profiles' => [
                        '_uid as user_profile_id',
                        'profile_picture',
                        'updated_at',
                    ],
                ])
            )
            ->shodh($search, $searchable)
            ->get()
            ->toArray();

        $userCoverPhotos = User::leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            ->whereNotNull('user_profiles.cover_picture')
            ->select(
                __nestedKeyValues([
                    'users' => [
                        '_id',
                        '_uid',
                        'first_name',
                        'last_name',
                        'username',
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS full_name"),
                    ],
                    'user_profiles' => [
                        '_uid as user_profile_id',
                        'cover_picture',
                        'updated_at',
                    ],
                ])
            )
            ->shodh($search, $searchable)
            ->get()
            ->toArray();

        $collection = collect(array_merge($userPhotos, $userProfilePhotos, $userCoverPhotos));
        $pageLength = isset($request['length']) ? $request['length'] : 100;
        $order = isset($request['order']) ? $request['order'] : null;

        // if order is set
        if (! __isEmpty($order)) {
            $columns = $request['columns'];
            $sortBy = $columns[$order[0]['column']]['data'];
            $sortOrder = $order[0]['dir'];

            if ($sortOrder == 'asc') {
                return     $collection->sortBy($sortBy)
                    ->paginate($pageLength)
                    ->toArray();
            } else {
                return     $collection->sortByDesc($sortBy)
                    ->paginate($pageLength)
                    ->toArray();
            }
        }

        return  $collection->paginate($pageLength)->toArray();
    }

    /**
     * Store user profile
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function getUsersPhoto($userID, $photoUid)
    {
        return UserPhotosModel::where([
            'users__id' => $userID,
            '_uid' => $photoUid,
        ])->first();
    }

    /**
     * Delete User
     *
     * @param  object  $user
     * @return bool
     *---------------------------------------------------------------- */
    public function deleteUserPhoto($user)
    {
        // Check if information deleted
        if ($user->delete()) {
            activityLog($user->first_name.' '.$user->last_name.' user photo deleted.');

            return true;
        }

        return false;
    }
}
