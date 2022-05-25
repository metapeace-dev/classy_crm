<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstallScheduleAttendee extends Model
{
    protected $guarded = ['id'];
    protected $table = 'install_schedule_attendees';

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }
}
