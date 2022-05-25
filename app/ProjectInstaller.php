<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ProjectInstaller extends Model
{
    use Notifiable;
    protected $guarded = ['id'];
    public function routeNotificationForMail()
    {
        return $this->user->email;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public static function byProject($id){
        return ProjectInstaller::join('users', 'users.id', '=', 'project_installers.user_id')
            ->where('project_installers.project_id', $id)
            ->where('users.status','active')
            ->get();
    }

    public static function checkIsMember($projectId, $userId){
        return ProjectInstaller::where('project_id', $projectId)
            ->where('user_id', $userId)->first();
    }
}
