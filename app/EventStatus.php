<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventStatus extends Model
{
    protected $table = 'event_status';

    protected $guarded = ['id'];

    public function events(){
        return $this->belongsTo(Event::class, 'status_id');
    }
}
