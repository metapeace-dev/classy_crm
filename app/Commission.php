<?php

namespace App;

use App\Traits\EnumValue;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use EnumValue;
    protected $table =  'commissions';
    protected $dates = ['pay_start_date', 'pay_end_date'];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function payment(){
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
