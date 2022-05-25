<?php

namespace App\Http\Requests\Admin\Client;

use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends CoreRequest
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
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'cell' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' => 'nullable|email',
            'second_email' => 'nullable|email'
        ];

        return $rules;
    }
}
