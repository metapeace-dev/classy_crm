<?php

namespace App\Http\Requests\InstallSchedules;

use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchedule extends CoreRequest
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
            'schedule_name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
//            'user_id' => 'required',
            'project_id' => 'required_unless:type_id,2'
        ];
        return $rules;
    }

    public function messages() {
        return [
            'schedule_name.required' => __('Schedule name field is required.'),
            'project_id.required' => __('Project field is required.')
        ];
    }
}
