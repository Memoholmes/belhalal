<?php
/**
* MessageRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Messenger\Requests;

use App\Yantrana\Base\BaseRequest;

class MessageRequest extends BaseRequest
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
        $rules = [];

        // Uploaded file
        if ($inputData['type'] != 2) {
            $rules = [
                'message' => 'required',
            ];
        }

        return $rules;
    }
}
