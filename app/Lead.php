<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    public function getFullNameAttribute()
    {
        return ucfirst($this->first_name)." ".ucfirst($this->last_name);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lead_source(){
        return $this->belongsTo(LeadSource::class, 'source_id');
    }
    public function lead_status(){
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }
    // public function client() {
    //     return $this->belongsTo(Client::class, 'client_id');
    // }
    public function follow() {
        return $this->hasMany(LeadFollowUp::class);
    }
    public function files() {
        return $this->hasMany(LeadFiles::class);
    }
    public function project_location() {
        return $this->belongsTo(ProjectLocation::class,'project_location_id');
    }

    public function project() {
        return $this->belongsTo(Project::class,'project_id');
    }
}
