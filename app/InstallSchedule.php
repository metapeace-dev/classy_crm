<?php

namespace App;

use App\Traits\EnumValue;
use Illuminate\Database\Eloquent\Model;

class InstallSchedule extends Model
{
    use EnumValue;
    protected $dates = ['start_date_time', 'end_date_time'];

    public function attendees(){
        return $this->hasMany(InstallScheduleAttendee::class, 'schedule_id');
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function color(){
        return $this->belongsTo(DesignerColor::class, 'designer_color_id');
    }

    public function type(){
        return $this->belongsTo(InstallScheduleType::class, 'type_id');
    }

    public function getUsers(){
        $userArray = [];
        foreach ($this->attendee as $attendee) {
            array_push($userArray, $attendee->user()->select('id', 'email', 'name')->first());
        }
        return collect($userArray);
    }
}
