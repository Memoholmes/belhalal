<?php
/**
* UserAddRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserAddRequest extends BaseRequest
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
        return [
            'first_name' => 'required|min:3|max:45',
            'last_name' => 'required|min:3|max:45',
            'username' => 'required|min:5|max:45|unique:users,username',
            'mobile_number' => 'required|max:15|unique:users,mobile_number',
            'email' => 'required|email|unique:users,email',
            'designation' => 'required|max:45',
            'password' => 'required|min:6|max:30',
            'confirm_password' => 'required|min:6|max:30|same:password',
        ];
    }
}
