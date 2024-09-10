<?php
/**
* StickerEditRequest.php - Request file
*
* This file is part of the Item component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Requests;

use App\Yantrana\Base\BaseRequest;
use Illuminate\Validation\Rule;

class StickerEditRequest extends BaseRequest
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
        $inputData = $this->all();

        $rules = [
            'title' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('items', 'title')->ignore(request()->route('stickerUId'), '_uid')->where(function ($query) {
                    return $query->where('status', 2);
                }),
            ],
            'premium_price' => 'required|integer',
        ];

        if (! __ifIsset($inputData['is_for_premium_user'])) {
            $rules['normal_price'] = 'required|integer|gte:premium_price';
        }

        return $rules;
    }
}
