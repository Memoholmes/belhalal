<?php
/**
* ApiUserResetPasswordRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class ApiUserResetPasswordRequest extends BaseRequest
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
     * Get the validation rules that apply to the user reset password request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return  [
            'otp' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|max:30',
            'password_confirmation' => 'required|min:6|max:30|same:password',
        ];
    }
}
