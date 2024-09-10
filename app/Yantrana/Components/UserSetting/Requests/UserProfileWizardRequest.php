<?php
/**
* UserProfileWizardRequest.php - Request file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\UserSetting\Requests;

use App\Yantrana\Base\BaseRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class UserProfileWizardRequest extends BaseRequest
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
        $date = appTimezone(Carbon::now());

        return [
            'gender' => [
                'sometimes',
                'required',
                Rule::in(array_keys(configItem('user_settings.gender'))),
            ],
            'birthday' => 'sometimes|required|validate_age',
        ];
    }

    /**
     * Get the validation rules that apply to the user register request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function messages()
    {
        $ageRestriction = configItem('age_restriction');

        return [
            'birthday.validate_age' => strtr('Age must be between __min__ and __max__ years', [
                '__min__' => $ageRestriction['minimum'],
                '__max__' => $ageRestriction['maximum'],
            ]),
        ];
    }

    /**
     * Call before validation process
     *
     * @example uses
    }
     * @return void
     *------------------------------------------------------------------------ */
    protected function processBefore()
    {
        parent::processBefore();

        $inputData = $this->all();

        if (isset($inputData['gender']) and __isEmpty($inputData['gender'])) {
            unset($inputData['gender']);
        }

        if (isset($inputData['birthday']) and __isEmpty($inputData['birthday'])) {
            unset($inputData['birthday']);
        }

        // replace input array
        $this->replace($inputData);
    }
}
