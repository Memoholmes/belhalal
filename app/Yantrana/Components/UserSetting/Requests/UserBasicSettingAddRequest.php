<?php
/**
* UserBasicSettingAddRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Requests;

use App\Yantrana\Base\BaseRequest;
use Carbon\Carbon;
use App\Yantrana\Components\User\Models\User as UserModel;

class UserBasicSettingAddRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the user register request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {  
         $inputData = $this->all();

        // Combine country code and mobile number
        $mobileData = '0' . $inputData['country_code'] . '-' . $inputData['mobile_number'];
        
        // Get the current user
        $user = auth()->user();

        $mobileData = '0'.$inputData['country_code'].'-'.$inputData['mobile_number'];
        $date = appTimezone(Carbon::now());

        return [
            'first_name' => 'required|min:3|max:45',
            'last_name' => 'required|min:3|max:45',
            'birthday' => 'sometimes|validate_age',
            'country_code' => 'required|min:1|max:5',
           'mobile_number' => [
                'required',
                'min:8',
                'max:15',
                function ($attribute, $value, $fail) use ($mobileData, $user) {
                    // Check if mobile number has changed and if it already exists in the database
                    if ($user->mobile_number !== $mobileData && UserModel::where('mobile_number', $mobileData)->exists()) {
                        $fail('This mobile number has already been taken.');
                    }
                }
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the user register request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function messages()
    {
        $ageRestriction = configItem('age_restriction');

        return [
            'birthday.validate_age' => __tr('Age must be between __min__ and __max__ years', [
                '__min__' => $ageRestriction['minimum'],
                '__max__' => $ageRestriction['maximum'],
            ]),
        ];
    }
}
