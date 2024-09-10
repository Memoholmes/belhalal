<?php
/**
* GenerateFakeUsers.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Requests;

use App\Yantrana\Base\BaseRequest;

class GenerateFakeUsers extends BaseRequest
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
        $recordsLimit = configItem('fake_data_generator.records_limits');

        return [
            'number_of_users' => "required|lte:$recordsLimit",
            'default_password' => 'required|min:6',
            'age_from' => 'required|gte:18|lte:80',
            'age_to' => 'required|gte:18|lte:80',
        ];
    }
}
