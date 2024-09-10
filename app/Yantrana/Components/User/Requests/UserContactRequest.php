<?php
/**
* UserContactRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class UserContactRequest extends BaseRequest
{
    /**
     * Unsecured/Un encrypted form fields.
     *------------------------------------------------------------------------ */
    protected $unsecuredFields = [
        'message',
    ];

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
            'fullName' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ];
    }
}
