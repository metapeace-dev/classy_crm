<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;
use App\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StoreTask extends CoreRequest
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
        $setting = global_setting();
        $rules = [
            'heading' => 'required',
            'start_date' => 'required',
            'due_date' => 'required|date_format:"'.$setting->date_format.'"|after_or_equal:start_date',
            'user_id.0' => 'required',
            'priority' => 'required'
        ];

        if($this->has('repeat') && $this->repeat == 'yes')
        {
            $rules['repeat_cycles'] = 'required|numeric';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.required' => __('messages.chooseProject'),
            'user_id.0.required' => 'Choose an assignee'
        ];
    }
}
