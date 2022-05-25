<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectLocation extends Model
{
    protected $table = 'project_location';

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }
}
