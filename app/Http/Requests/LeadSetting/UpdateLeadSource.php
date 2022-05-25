<?php

namespace App\Http\Requests\LeadSetting;

use Froiden\LaravelInstaller\Request\CoreRequest;

class UpdateLeadSource extends CoreRequest
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
            'name' => 'required|unique:lead_sources,name,'.$this->route()->parameter('id'),
        ];
    }
}
