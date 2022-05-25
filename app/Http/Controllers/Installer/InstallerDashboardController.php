<?php

namespace App\Http\Controllers\Installer;

use App\AttendanceSetting;
use App\DashboardPeriod;
use App\DashboardWidget;
use App\Event;
use App\Project;
use App\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallerDashboardController extends InstallerBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = __('app.menu.dashboard');
        $this->pageIcon = 'icon-speedometer';

        // Getting Attendance setting data
        $this->attendanceSettings = AttendanceSetting::first();

        //Getting Maximum Check-ins in a day
        $this->maxAttandenceInDay = $this->attendanceSettings->clockin_in_day;
    }

    public function index()
    {
        $this->period = DashboardPeriod::where('id', 1)->first();
        if($this->period){
            if($this->period->period == 1){
                $this->fromDate = Carbon::today()->subMonths(1);
                $this->toDate = Carbon::today();
            }
            else if( $this->period->period == 2){
                $this->fromDate = Carbon::today()->subMonths(2);
                $this->toDate = Carbon::today();
            }
            else{
                $this->fromDate = Carbon::today()->subMonths(3);
                $this->toDate = Carbon::today();
            }
        }
        else{
            $this->period = new DashboardPeriod();
            $this->period->timestamps = false;
            $this->period->period = 1;
            $this->period->save();
            $this->fromDate = Carbon::today()->subMonths(1);
            $this->toDate = Carbon::today();
        }

        $toDateStr = $this->toDate->addDay(1)->toDateString();
        $this->counts = DB::table('users')
            ->select(
                DB::raw('(select count(leads.id) from `leads` where `leads`.user_id = '.auth()->user()->id.' and `leads`.created_at BETWEEN CAST(\''.$this->fromDate->toDateString().'\' AS DATE) AND CAST(\''.$toDateStr.'\' AS DATE)) as totalLeads'),
                DB::raw('(select count(leads.id) from `leads` where `leads`.user_id = '.auth()->user()->id.' and `leads`.status_id = 1 AND `leads`.created_at BETWEEN CAST(\''.$this->fromDate->toDateString().'\' AS DATE) AND CAST(\''.$toDateStr.'\' AS DATE)) as pendingLeads'),
                DB::raw('(select count(projects.id) from `projects` where `projects`.user_id = '.auth()->user()->id.' and `projects`.created_at BETWEEN CAST(\''.$this->fromDate->toDateString().'\' AS DATE) AND CAST(\''.$toDateStr.'\' AS DATE)) as totalProjects'),
                DB::raw('(select sum(projects.sales_price) from `projects` where `projects`.user_id = '.auth()->user()->id.' and `projects`.created_at BETWEEN CAST(\''.$this->fromDate->toDateString().'\' AS DATE) AND CAST(\''.$toDateStr.'\' AS DATE)) as totalSales')
            )
            ->first();

        $this->userTasks = Task::join('task_attendees', 'task_attendees.task_id', '=', 'tasks.id')->where('task_attendees.user_id', auth()->user()->id)->where('due_date', '>=' , $this->fromDate)->get();
        $this->appointments = Event::join('event_attendees', 'event_attendees.event_id', '=', 'events.id')->where('event_attendees.user_id', auth()->user()->id)->where('start_date_time', '<=', Carbon::today()->endOfDay()->toDateTimeString())->where('start_date_time', '>=', Carbon::today()->startOfDay()->toDateTimeString())->orderBy('start_date_time','asc')->get();
        $this->installs = Project::where('user_id', auth()->user()->id)->where('install_start_date', '<', Carbon::tomorrow()->toDateString())->where('install_start_date', '>', Carbon::yesterday()->toDateString())->orderBy('install_start_date','asc')->get();
        $this->widgets = DashboardWidget::all();
        $this->activeWidgets = DashboardWidget::where('status', 1)->get()->pluck('widget_name')->toArray();
        return view('installer.dashboard.index', $this->data);
    }
}
