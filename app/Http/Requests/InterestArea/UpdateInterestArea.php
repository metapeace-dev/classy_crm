<?php

namespace App\Http\Requests\InterestArea;

use Froiden\LaravelInstaller\Request\CoreRequest;

class UpdateInterestArea extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|unique:interest_areas,type,'.$this->route()->parameter('id'),
        ];
    }
}
