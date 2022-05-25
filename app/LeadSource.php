<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LeadSource extends Model
{
    protected $table = 'lead_sources';

    protected $guarded = ['id'];

    public function leads() {
        return $this->hasMany(Lead::class, 'lead_source_id');
    }
}
