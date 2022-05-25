<?php

namespace App;

use App\Traits\CustomFieldsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use CustomFieldsTrait;
    use SoftDeletes;

    protected $dates = ['start_date', 'deadline'];

    protected $guarded = ['id'];

    protected $appends = ['isProjectAdmin'];

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'project_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function installers()
    {
        return $this->hasMany(ProjectInstaller::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id')->orderBy('id', 'desc');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id')->orderBy('id', 'desc');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'project_id')->orderBy('id', 'desc');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'project_id')->orderBy('id', 'desc');
    }

    public function times()
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_id')->orderBy('id', 'desc');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'project_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'project_id');
    }

    /**
     * @return bool
     */

    public static function checkIsMember($projectId){
        return Project::where('id', $projectId)
            ->where('user_id', auth()->user()->id)->count();
    }

    public function checkProjectUser()
    {
        $project = ProjectInstaller::where('project_id', $this->id)
            ->where('user_id', auth()->user()->id)
            ->count();

        if ($project > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function checkProjectClient()
    {
        $project = Project::where('id', $this->id)
            ->where('client_id', auth()->user()->id)
            ->count();

        if ($project > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function clientProjects($clientId)
    {
        return Project::where('client_id', $clientId)->get();
    }

    public static function byEmployee($employeeId)
    {
        return Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', $employeeId)
            ->get();
    }

//    public function scopeCompleted($query)
//    {
//        return $query->where('completion_percent', '100');
//    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in progress');
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', 'on hold');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not started');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeOverdue($query)
    {
        $setting = Setting::first();
        return $query->where('completion_percent', '<>', '100')
            ->where('deadline', '<', Carbon::today()->timezone($setting->timezone));
    }

    public function getIsProjectAdminAttribute()
    {
        if ($this->project_admin == auth()->user()->id) {
            return true;
        }
        return false;
    }

    public function appointments(){
        $appointments = Event::where('project_id', $this->id);
        if(!empty($this->lead->id)){
            $appointments->orWhere('lead_id', $this->lead->id);
        }
        $appointments = $appointments->get();
        return $appointments;
    }
}
