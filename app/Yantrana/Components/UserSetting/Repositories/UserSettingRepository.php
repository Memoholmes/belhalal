<?php
/**
* UserSettingRepository.php - Repository file
*
* This file is part of the UserSetting component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\User\Models\ProfileBoost;
use App\Yantrana\Components\User\Models\User;
use App\Yantrana\Components\User\Models\UserProfile;
use App\Yantrana\Components\User\Models\UserSubscription;
use App\Yantrana\Components\UserSetting\Interfaces\UserSettingRepositoryInterface;
use App\Yantrana\Components\UserSetting\Models\CityModel;
use App\Yantrana\Components\UserSetting\Models\UserPhotosModel;
use App\Yantrana\Components\UserSetting\Models\UserSettingModel;
use App\Yantrana\Components\UserSetting\Models\UserSpecificationModel;
use App\Yantrana\Support\CommonTrait;
use Carbon\Carbon;
use DB;

class UserSettingRepository extends BaseRepository implements UserSettingRepositoryInterface
{
    /**
     * @var CommonTrait - Common Trait
     */
    use CommonTrait;

    /**
     * Fetch All Record from Cache
     *
     * @param  array  $names
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserSettingByName($name)
    {
        return UserSettingModel::whereIn('key_name', $name)
            ->select('_id', 'key_name', 'value', 'data_type', 'users__id')
            ->where('users__id', getUserID())
            ->get();
    }

    /**
     * Store or update user setting data
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeOrUpdate($inputData)
    {
        // Check if data updated or inserted
        if (UserSettingModel::bunchInsertUpdate($inputData, '_id')) {
            return true;
        }

        return false;
    }

    /**
     * Delete user setting by keys
     *
     * @param  array  $userSettingKeyNames
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function deleteUserSetting($userSettingKeyNames)
    {
        if (UserSettingModel::whereIn('_id', $userSettingKeyNames)->delete()) {
            return true;
        }

        return false;
    }

    /**
     * Fetch the record of UserSetting
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserDetails($userId)
    {
        return User::where('users._id', $userId)
            ->leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            ->select(
                \__nestedKeyValues([
                    'users' => [
                        '_id',
                        'username',
                        'email',
                        'first_name',
                        'last_name',
                        'designation',
                        'mobile_number',
                    ],
                    'user_profiles' => [
                        '_id AS user_profile_id',
                        'users__id',
                        'countries__id',
                        'profile_picture',
                        'gender',
                        'dob',
                        'city',
                        'about_me',
                        'location_latitude',
                        'location_longitude',
                        'preferred_language',
                        'relationship_status',
                        'work_status',
                        'education',
                        'cover_picture',
                    ],
                ])
            )
            ->first();
    }

    /**
     * Fetch User profile
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserProfile($userId)
    {
        return UserProfile::where('users__id', $userId)->first();
    }

    /**
     * Update User
     *
     * @param  object  $user
     * @param  array  $updateData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function updateUser($user, $updateData)
    {
        if ($user->modelUpdate($updateData)) {
            return true;
        }

        return false;
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
            'users__id' => $inputData['user_id'],
            'countries__id',
            'gender' => array_get($inputData, 'gender'),
            'dob' => array_get($inputData, 'dob'),
            'about_me',
            'city',
            'work_status',
            'education',
            'preferred_language',
            'relationship_status',
            'location_latitude',
            'location_longitude',
        ];
        $userProfile = new UserProfile();
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
     * Fetch user specification by user id
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserSpecificationById($userId)
    {
        return UserSpecificationModel::where('users__id', $userId)->get();
    }

    /**
     * Store or update user specification data
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeOrUpdateUserSpecification($inputData)
    {
        // Check if data updated or inserted
        if (UserSpecificationModel::bunchInsertUpdate($inputData, '_id')) {
            return true;
        }

        return false;
    }

    /**
     * Fetch user photos
     *
     * @param  number  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserPhotos($userId)
    {
        return UserPhotosModel::where('users__id', $userId)->get();
    }

    /**
     * Fetch user photos
     *
     * @param  number  $photo id
     * @param  number  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUserPhotosById($photoUid)
    {
        return UserPhotosModel::where([
            '_uid' => $photoUid,
            'users__id' => getUserID(),
        ])->first();
    }

    /**
     * Store user photos
     *
     * @param  array  $storeData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeUserPhoto($storeData)
    {
        $keyValues = [
            'status' => 1,
            'users__id',
            'file',
        ];

        $newUserPhotosModel = new UserPhotosModel();

        if ($newUserPhotosModel->assignInputsAndSave($storeData, $keyValues)) {
            return $newUserPhotosModel;
        }

        return false;
    }

    /**
     * Delete photo.
     *
     * @param  object  $photo
     * @return bool|object
     *---------------------------------------------------------------- */
    public function deletePhoto($userPhoto)
    {
        // Check if page deleted
        if ($userPhoto->delete()) {
            return  $userPhoto;
        }

        return false;
    }

    /**
     * Fetch Filter data
     *
     * @param  array  $filterData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchFilterData($filterData, $ignoreUserIds, $paginateCount = false)
    {
        if (! $paginateCount) {
            $paginateCount = configItem('user_settings.search_pagination');
        }

        // prepare dates for comparison
        $minAgeDate = Carbon::today()->subYears($filterData['min_age'])->toDateString();
        $maxAgeDate = Carbon::today()->subYears($filterData['max_age'])->endOfDay()->toDateString();
        $currentDate = Carbon::now();

        $searchQuery = UserProfile::basicFilter($filterData)
            ->join('users', 'user_profiles.users__id', '=', 'users._id')
            ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
            ->leftJoin('user_specifications', 'user_profiles.users__id', '=', 'user_specifications.users__id')
            ->leftJoin('user_subscriptions', function ($subscriptionJoin) use ($currentDate) {
                $subscriptionJoin->on('user_profiles.users__id', '=', 'user_subscriptions.users__id')
                    ->where('user_subscriptions.expiry_at', '>', $currentDate);
            })
            ->leftJoin('profile_boosts', function ($profileBoostJoin) use ($currentDate) {
                $profileBoostJoin->on('user_profiles.users__id', '=', 'profile_boosts.for_users__id')
                    ->where('profile_boosts.expiry_at', '>', $currentDate);
            })
            ->leftJoin('countries', 'user_profiles.countries__id', '=', 'countries._id')
            ->groupBy('users._id')
            ->where('users.status', 1)
            ->whereNotIn('users._id', $ignoreUserIds)
            ->select(
                __nestedKeyValues([
                    'users' => [
                        '_id AS user_id',
                        '_uid AS user_uid',
                        'username',
                        'first_name',
                        'last_name',
                        'is_fake',
                    ],
                    'user_profiles' => [
                        'created_at',
                        'updated_at',
                        'profile_picture',
                        'cover_picture',
                        'gender',
                        'dob',
                        'countries__id',
                        'users__id',
                    ],
                    'profile_boosts' => [
                        'created_at as profileBoostCreatedAt',
                        'for_users__id',
                        'for_users__id as profileBoostIds',
                    ],
                    'user_subscriptions' => [
                        'created_at as premiumUserCreatedAt',
                        'users__id as premiumUserIds',
                    ],
                    'countries' => [
                        'name as countryName',
                    ],
                    'user_authorities' => [
                        'updated_at AS user_authority_updated_at',
                    ],
                    'user_specifications' => [
                        'specification_key',
                        'specification_value',
                    ],
                ])
            );
        $filterData['name']  = $filterData['name'] ?? '';
        $filterData['username']  = $filterData['username'] ?? '';
        $filteredSearchQuery = $filterData['name'] ?? '';
        $usernameFilteredSearchQuery = $filterData['username'];

        if (!__isEmpty($filteredSearchQuery)) {
            $searchQuery->where(function ($query) use ($filteredSearchQuery) {
                $filteredSearchQuery = '%' . $filteredSearchQuery . '%';
                $query->where('users.first_name', 'LIKE', $filteredSearchQuery)
                    ->orWhere('users.last_name', 'LIKE', $filteredSearchQuery);
            });
        }

        if (!__isEmpty($usernameFilteredSearchQuery)) {
            $searchQuery->where(function ($query) use ($usernameFilteredSearchQuery) {
                $usernameFilteredSearchQuery = '%' . $usernameFilteredSearchQuery . '%';
                $query->where('users.username', 'LIKE', $usernameFilteredSearchQuery);
            });
        }

        if (($filterData['distance'] != null) and ($filterData['distance'] != '')) {
            $searchQuery->distanceFilter($filterData);
        }


        $userSpecifications = $this->getUserSpecificationConfig();
        $checkKeysInDb = [];
        foreach ($userSpecifications['groups'] as $specifications) {
            if(isset($specifications['items'])) {
                foreach ($specifications['items'] as $itemKey => $item) {
                    if ($item['input_type'] == 'select') {
                        $checkKeysInDb[] = $itemKey;
                    }
                }
            }
        }

        $userProfileColumns = [
            'language',
            'relationship_status',
            'work_status',
            'education',
        ];

        if (! __isEmpty($filterData)) {
            foreach ($filterData as $specKey => $specValue) {
                if (! __isEmpty($specValue)) {
                    if (is_array($specValue)) {
                        if (in_array($specKey, $userProfileColumns)) {
                            if ($specKey == 'language') {
                                $searchQuery->whereIn('user_profiles.preferred_language', $specValue);
                            } else {
                                $searchQuery->whereIn('user_profiles.'.$specKey, $specValue);
                            }
                        } else {
                            if (in_array($specKey, $checkKeysInDb)) {
                                $searchQuery->whereHas('user_specifications', function ($q) use ($specKey, $specValue) {
                                    $q->where('user_specifications.specification_key', $specKey)
                                        ->whereIn('user_specifications.specification_value', array_keys($specValue));
                                });
                            }
                        }
                    } elseif (isset($filterData['min_height']) and isset($filterData['max_height']) and ! __isEmpty($filterData['min_height']) and ! __isEmpty($filterData['max_height'])) {
                        $searchQuery->where(function ($heightQuery) use ($filterData) {
                            $heightQuery->where('user_specifications.specification_key', 'height')
                                ->whereBetween('user_specifications.specification_value', [
                                    $filterData['min_height'],
                                    $filterData['max_height'],
                                ]);
                        });
                    } else {
                        if (in_array($specKey, $checkKeysInDb)) {
                            $searchQuery->where([
                                'specification_key' => $specKey,
                                'specification_value' => $specValue,
                            ]);
                        }
                    }
                }
            }
        }

        return $searchQuery->latest('profileBoostCreatedAt')
            ->latest('premiumUserCreatedAt')
            ->latest('user_profiles.updated_at')
            ->paginate($paginateCount);
    }

    /**
     * Fetch Filter Random User data
     *
     * @param  array  $filterData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchFilterRandomUser($filterData, $ignoreUserIds, $userType)
    {
        // prepare dates for comparison
        $currentDate = Carbon::now();
        $searchQuery = UserProfile::basicFilter($filterData)
            ->join('users', 'user_profiles.users__id', '=', 'users._id')
            ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
            ->leftJoin('countries', 'user_profiles.countries__id', '=', 'countries._id')
            ->groupBy('users._id')
            ->where('users.status', 1)
            ->whereNotIn('users._id', $ignoreUserIds);
        //check distance not equal to null
        if ($filterData['distance'] != null) {
            $searchQuery->distanceFilter($filterData);
        }

        if ($userType == 'boosterUser') {
            return $searchQuery->join('profile_boosts', function ($profileBoostJoin) use ($currentDate) {
                $profileBoostJoin->on('user_profiles.users__id', '=', 'profile_boosts.for_users__id')
                    ->where('profile_boosts.expiry_at', '>', $currentDate);
            })
                ->select(
                    'users._id AS user_id',
                    'users._uid AS user_uid',
                    'users.username',
                    'users.first_name',
                    'users.last_name',
                    'users.is_fake',
                    'user_profiles.created_at',
                    'user_profiles.updated_at',
                    'user_profiles.profile_picture',
                    'user_profiles.cover_picture',
                    'user_profiles.gender',
                    'user_profiles.dob',
                    'user_profiles.countries__id',
                    'user_profiles.users__id',
                    'profile_boosts.created_at as profileBoostCreatedAt',
                    'profile_boosts.for_users__id',
                    'profile_boosts.for_users__id as profileBoostIds',
                    'countries.name as countryName',
                    'user_authorities.updated_at AS user_authority_updated_at'
                )
                ->latest('profileBoostCreatedAt')
                ->get();
        } elseif ($userType == 'premiumUser') {
            return $searchQuery->join('user_subscriptions', function ($subscriptionJoin) use ($currentDate) {
                $subscriptionJoin->on('user_profiles.users__id', '=', 'user_subscriptions.users__id')
                    ->where('user_subscriptions.expiry_at', '>', $currentDate);
            })
                ->select(
                    'users._id AS user_id',
                    'users._uid AS user_uid',
                    'users.username',
                    'users.first_name',
                    'users.last_name',
                    'users.is_fake',
                    'user_profiles.created_at',
                    'user_profiles.updated_at',
                    'user_profiles.profile_picture',
                    'user_profiles.cover_picture',
                    'user_profiles.gender',
                    'user_profiles.dob',
                    'user_profiles.countries__id',
                    'user_profiles.users__id',
                    'user_subscriptions.created_at as premiumUserCreatedAt',
                    'user_subscriptions.users__id as premiumUserIds',
                    'user_subscriptions.expiry_at',
                    'countries.name as countryName',
                    'user_authorities.updated_at AS user_authority_updated_at'
                )
                ->latest('premiumUserCreatedAt')
                ->get();
        } elseif ($userType == 'normalUser') {
            return $searchQuery->select(
                'users._id AS user_id',
                'users._uid AS user_uid',
                'users.username',
                'users.first_name',
                'users.last_name',
                'users.is_fake',
                'user_profiles.created_at',
                'user_profiles.updated_at',
                'user_profiles.profile_picture',
                'user_profiles.cover_picture',
                'user_profiles.gender',
                'user_profiles.dob',
                'user_profiles.countries__id',
                'user_profiles.users__id',
                'countries.name as countryName',
                'user_authorities.updated_at AS user_authority_updated_at'
            )
                ->latest('user_profiles.updated_at')
                ->get();
        }
    }

    /**
     * fetch active profile boost user.
     *
     * @param  array  $userID
     *
     *-----------------------------------------------------------------------*/
    public function fetchAllBoostUsersIgIds($igUsersIds)
    {
        $currentTime = Carbon::now();

        //if data exist then show records else return blank array
        return ProfileBoost::whereNotIn('for_users__id', $igUsersIds)
            ->where('expiry_at', '>=', $currentTime)
            ->get();
    }

    /**
     * Fetch user subscription data by logged in user id.
     *
     * @param  number  $userID
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchAllPremiumUsersIgIds($ignoreUsersIds)
    {
        $currentTime = Carbon::now();

        return UserSubscription::select(
            __nestedKeyValues([
                'user_subscriptions.*',
            ])
        )
            ->where('user_subscriptions.expiry_at', '>=', $currentTime)
            ->whereNotIn('user_subscriptions.users__id', $ignoreUsersIds)
            ->get();
    }

    /**
     * Fetch Random User Data
     *
     * @param  array  $filterData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchRandomUserData($filterData, $ignoreUserIds)
    {
        // prepare dates for comparison
        $minAgeDate = Carbon::today()->subYears($filterData['min_age'])->toDateString();
        $maxAgeDate = Carbon::today()->subYears($filterData['max_age'])->endOfDay()->toDateString();

        // $tempArray = [64,25,15,57];
        // $tempStr = implode(',', $tempArray);

        $randomSearchQuery = UserProfile::whereIn('gender', $filterData['looking_for'])
            ->join('users', 'user_profiles.users__id', '=', 'users._id')
            ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
            ->leftJoin('countries', 'user_profiles.countries__id', '=', 'countries._id')
            ->where('users.status', 1)
            ->whereNotIn('users._id', $ignoreUserIds)
            ->whereBetween('user_profiles.dob', [$maxAgeDate, $minAgeDate]);
        if ($filterData['distance'] != null) {
            $randomSearchQuery->distanceFilter($filterData);
        }

        $randomSearchQuery->select(
            __nestedKeyValues([
                'users' => [
                    '_id AS user_id',
                    '_uid AS user_uid',
                    'username',
                    'first_name',
                    'last_name',
                    'is_fake',
                ],
                'user_profiles' => [
                    'created_at',
                    'updated_at',
                    'profile_picture',
                    'cover_picture',
                    'gender',
                    'dob',
                    'countries__id',
                    'users__id',
                ],
                'countries' => [
                    'name as countryName',
                ],
                'user_authorities' => [
                    'updated_at AS user_authority_updated_at',
                ],
            ])
        );

        return $randomSearchQuery->get();
    }

    /**
     * Search static cities
     *
     * @param  string  $searchQuery
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function searchStaticCities($searchQuery)
    {
        $searchable = [
            'cities.name',
            // 'states.name',
            // 'cities_full_name' => DB::raw("CONCAT(cities.name, states.name, countries.name)")
        ];

        return CityModel::select([
            DB::raw("CONCAT(cities.name, ', ', states.name, ', ', countries.name) AS cities_full_name"),
            'cities.id',
        ])
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'cities.country_code', '=', 'countries.iso_code')
            ->shodh($searchQuery, $searchable)
            ->take(100)
            ->get()
            ->toArray();
    }

    /**
     * Search static cities
     *
     * @param  string  $searchQuery
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchCity($cityId)
    {
        return CityModel::find($cityId);
    }
}
