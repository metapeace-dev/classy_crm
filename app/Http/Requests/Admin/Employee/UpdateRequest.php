<?php

namespace App\Http\Requests\Admin\Employee;

use App\EmployeeDetails;
use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

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
//        $detailID = EmployeeDetails::where('user_id', $this->route('employee'))->first();
        return [
            'email' => 'required|unique:users,email,'.$this->route('employee'),
            'slack_username' => 'nullable',//|unique:employee_details,slack_username,'.$detailID->id,
            'joining_date' => 'required',
            'name'  => 'required',
            'hourly_rate' => 'nullable|numeric',
            'department' => 'nullable',
            'designation' => 'nullable',
        ];
    }
}
