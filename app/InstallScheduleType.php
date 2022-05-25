<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstallScheduleType extends Model
{
    protected $table = 'install_schedule_types';
    protected $guarded = ['id'];

    public function schedules(){
        return $this->hasMany(InstallSchedule::class, 'type_id');
    }
}
