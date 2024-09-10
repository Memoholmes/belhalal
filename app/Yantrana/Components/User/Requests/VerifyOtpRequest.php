<?php
/**
* VerifyOtpRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class VerifyOtpRequest extends BaseRequest
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
        if(getStoreSettings('allow_recaptcha')){
            request()->validate(['g-recaptcha-response' => 'required'], [
                'g-recaptcha-response' => __tr('The recaptcha field is required.')
            ]);
        }
        return [
            'otp' => 'required',
        ];
    }
}
