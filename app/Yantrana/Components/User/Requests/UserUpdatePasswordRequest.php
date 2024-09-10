<?php
/**
* UserLoginRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserUpdatePasswordRequest extends BaseRequest
{

    /**
     * Secure form.
     *------------------------------------------------------------------------ */
    protected $securedForm = true;
    
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
     * Get the validation rules that apply to the user update password request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return  [
            'current_password' => 'required|min:6|max:30',
            'new_password' => 'required|min:6|max:30|different:current_password',
            'new_password_confirmation' => 'required|min:6|max:30|same:new_password',
        ];
    }
}
