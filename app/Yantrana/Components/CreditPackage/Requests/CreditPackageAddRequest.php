<?php
/**
* CreditPackageAddRequest.php - Request file
*
* This file is part of the CreditPackage component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\CreditPackage\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;

class CreditPackageAddRequest extends BaseRequest
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
     * Get the validation rules that apply to the add author client post request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return [
            'title' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('credit_packages', 'title'),
            ],
            'price' => 'required|integer',
            'credits' => 'required|integer',
            'package_image' => 'required',
        ];
    }
}
