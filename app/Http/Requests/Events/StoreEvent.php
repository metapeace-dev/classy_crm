<?php

namespace App\Http\Requests\Events;

use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvent extends CoreRequest
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
        $rules =  [
            'event_name' => 'required_if:event_type,1|required_if:event_type,2',
            'start_date' => 'required',
            'end_date' => 'required',
            'user_id' => 'required',
        ];
        if ($this->event_type == 1) {
            $rules['lead_id'] = 'required';
        }
        if ($this->event_type == 2) {
            $rules['lead_id'] = 'required_without_all:project_id,client_id';
            $rules['project_id'] = 'required_without_all:lead_id,client_id';
            $rules['client_id'] = 'required_without_all:project_id,lead_id';
        }
        return $rules;
    }

    public function messages() {
        return [
            'event_name.required_if' => __('Appointment name field is required.'),
            'project_id.required_without_all' => __('Project field is required.'),
            'lead_id.required_if' => __('Lead field is required.'),
            'lead_id.required_without_all' => __('Lead field is required.'),
            'client_id.required_without_all' => __('Client field is required.')
        ];
    }
}
