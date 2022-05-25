<?php

namespace App;

use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $guarded = [];

    public function projects() {
        return $this->hasMany(Project::class);
    }

    public function leads() {
        return $this->hasMany(Lead::class);
    }

    public function getFullNameAttribute()
    {
        return ucfirst($this->first_name)." ".ucfirst($this->last_name);
    }
}
