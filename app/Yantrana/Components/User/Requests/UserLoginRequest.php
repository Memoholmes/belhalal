<?php
/**
* UserLoginRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Http\Request;

class UserLoginRequest extends BaseRequest
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
     * Get the validation rules that apply to the user login request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        $inputData = Request::all();
        $email_username = $inputData['email_or_username'];
        if (str_contains($email_username, '@')) {
            $rules = [
                'email_or_username' => 'required|email',
                'password' => 'required|min:6',
            ];
        } else {
            $rules = [
                'email_or_username' => 'required',
                'password' => 'required|min:6',
            ];
        }

        return $rules;
    }

    /**
     * Get the validation rules that apply to the user login request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function messages()
    {
        $inputData = Request::all();
        $email_username = $inputData['email_or_username'];
        if (str_contains($email_username, '@')) {
            return [
                'email_or_username.email' => 'The email must be a valid email address',
            ];
        }

        return [];
    }
}
