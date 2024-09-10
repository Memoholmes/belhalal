<?php

/**
 * UserProfile.php - Model file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Country\Models\Country;
use App\Yantrana\Components\UserSetting\Models\UserSpecificationModel;
use Carbon\Carbon;

class UserProfile extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'user_profiles';

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '_id' => 'integer',
        'countries__id' => 'integer',
        'users__id' => 'integer',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * @var array, $inputData - Scope function for filtering distance data
     */
    public function scopeDistanceFilter($query, $inputData)
    {
        $distance = (! \__isEmpty($inputData['distance'])) ? $inputData['distance'] : 0;
        $latitude = $inputData['latitude'];
        $longitude = $inputData['longitude'];
        $measure = getStoreSettings('distance_measurement');
        $rawQuery = sprintf(
            '('.$measure.' * acos(cos(radians(%1$.7f)) * cos(radians(location_latitude)) * cos(radians(location_longitude) - radians(%2$.7f)) + sin(radians(%1$.7f)) * sin(radians(location_latitude))))',
            $latitude,
            $longitude
        );

        return $query
            ->selectRaw("{$rawQuery} AS distance")
            ->whereRaw("{$rawQuery} <= ?", [$distance]);
    }

    /**
     * @var array, $filterData - Scope function for filtering basic data
     */
    public function scopeBasicFilter($query, $filterData)
    {
        // prepare dates for comparison
        $minAgeDate = getAgeDate($filterData['min_age'], 'min', true)->toDateString();
        $maxAgeDate = getAgeDate($filterData['max_age'], 'max', true)->toDateString();

        if(isset($filterData['user_type']) && $filterData['user_type'] == '1'){
            $query->whereIn('is_verified', [1]);
        }
        return $query->whereIn('gender', $filterData['looking_for'])
            ->whereBetween('user_profiles.dob', [$maxAgeDate, $minAgeDate]);
    }

    /**
     * Get the profile record associated with the user.
     */
    public function country()
    {
        return $this->hasOne(Country::class, '_id', 'countries__id');
    }

    public function user_specifications()
    {
        return $this->hasMany(UserSpecificationModel::class, 'users__id', 'users__id');
    }
}
