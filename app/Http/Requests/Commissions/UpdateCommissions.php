<?php

namespace App\Http\Requests\Commissions;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissions extends CoreRequest
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
            'amount' => 'required',
            'pay_start_date' => 'required',
            'pay_end_date' => 'required|date_format:"'.$setting->date_format.'"|after_or_equal:pay_start_date',
            'project_id'=> 'required'
        ];
        if(!is_numeric(str_replace(',', '', $this->amount))){
            $rules['amount'] = 'numeric';
        }

        return $rules;
    }

    public function messages() {
        return [
            'invoice_id.required' => 'Select the invoice you want to add payment for.'
        ];
    }
}
