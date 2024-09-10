<?php
/**
* GiftAddRequest.php - Request file
*
* This file is part of the Item component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;

class GiftAddRequest extends BaseRequest
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
                Rule::unique('items', 'title')->where(function ($query) {
                    return $query->where('status', 1);
                }),
            ],
            'normal_price' => 'required|integer',
            'premium_price' => 'required|integer',
            'gift_image' => 'required',
        ];
    }
}
