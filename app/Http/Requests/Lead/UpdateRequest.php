<?php

namespace App\Http\Requests\Lead;

use Froiden\LaravelInstaller\Request\CoreRequest;

class UpdateRequest extends CoreRequest
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
        if(auth()->user()->hasRole('designer')){
            $rules = [
                'email' => 'nullable|email',
                'second_email' => 'nullable|email'
            ];
        }
        else{
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                'cell' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                'email' => 'nullable|email',
                'second_email' => 'nullable|email'
            ];
        }

        return $rules;
    }
}
