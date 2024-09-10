<?php
/**
* UserUpdateRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;
use App\Yantrana\Components\User\Models\User as UserModel;
use Illuminate\Support\Facades\DB;

class UserUpdateRequest extends BaseRequest
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
        $userId = $this->route('user'); 
        // Get the current user
        $user = auth()->user();

        $mobileData = '0'.$inputData['country_code'].'-'.$inputData['mobile_number'];
        $userUid = $this->route('userUid');

        return [
            'first_name' => 'required|min:3|max:45',
            'last_name' => 'required|min:3|max:45',
            'username' => 'required|min:5|max:45|'.Rule::unique('users')->ignore($userUid, '_uid'),
            'mobile_number' => [
                function ($attribute, $value, $fail) use ($userUid, $mobileData) {
                    $exists = DB::table('users')
                        ->where('mobile_number', $mobileData)
                        ->when($userUid, function ($query) use ($userUid) {
                            return $query->where('_uid', '!=', $userUid);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('This mobile number has already been taken.');
                    }
                },
            ],





            'email' => 'required|email|'.Rule::unique('users')->ignore($userUid, '_uid'),
        ];
    }

    /**
     * Get the validation rules that apply to the user register request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function messages()
    {
        return [];
    }
}
