<?php
/**
* UserChangeEmailRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserChangeEmailRequest extends BaseRequest
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
     * Get the validation rules that apply to the user change email request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        if (isMobileAppRequest()) {
            return  [
                'current_email' => 'required|email',
                'current_password' => 'required|min:6',
                'new_email' => 'required|email|unique_email|different:current_email',
            ];
        }

        return  [
            'current_password' => 'required|min:6',
            'new_email' => 'required|email|unique_email|different:current_email',
        ];
    }

    /**
     * custom messages.
     *
     *-----------------------------------------------------------------------*/
    public function messages()
    {
        return  [
            'new_email.unique_email' => 'The new email has already been taken',
        ];
    }
}
