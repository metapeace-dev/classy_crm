<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;

class StoreProject extends CoreRequest
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
                'second_email' => 'nullable|email',
                'sales_price' => 'nullable',
            ];
        }
        else{
            $rules = [
                'project_name' => 'required',
                'status' => 'required',
                'user_id' => 'required',
                'client_id' => 'required',
                'email' => 'nullable|email',
                'second_email' => 'nullable|email',
                'sales_price' => 'nullable',
                'discount' => 'nullable|numeric',
                'commission' => 'nullable|numeric'
            ];

            if($this->discount_type != 'dollar'){
                $rules['discount'] = 'nullable|numeric|max:100';
            }
            if($this->commission_type != 'dollar'){
                $rules['commission'] = 'nullable|numeric|max:100';
            }
        }

        return $rules;
    }
}
