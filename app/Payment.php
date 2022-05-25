<?php

namespace App;

use App\Traits\EnumValue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use EnumValue;
    protected $dates = ['paid_on'];

    protected $appends = ['total_amount', 'paid_date'];

    public function commission(){
        return $this->hasOne(Commission::class);
    }
   
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getTotalAmountAttribute()
    {

        if (!is_null($this->amount) && !is_null($this->currency_symbol) && !is_null($this->currency_code)) {
            return  $this->amount;
        }

        return "";
    }

    public function getPaidDateAttribute()
    {
        if (!is_null($this->paid_on)) {
            return Carbon::parse($this->paid_on)->format('d F, Y H:i A');
        }
        return "";
    }
}
