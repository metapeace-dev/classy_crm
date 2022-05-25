<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\DesignerColor;
use App\Helper\Reply;
use App\Http\Requests\InstallSchedules\MoveSchedule;
use App\Http\Requests\InstallSchedules\StoreSchedule;
use App\Http\Requests\InstallSchedules\UpdateSchedule;
use App\InstallSchedule;
use App\InstallScheduleAttendee;
use App\InstallScheduleType;
use App\InterestArea;
use App\Lead;
use App\Note;
use App\Notifications\EventEmail;
use App\Project;
use App\ProjectInstaller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class AdminInstallScheduleController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.installSchedules');
        $this->pageIcon = 'icon-calender';
        $this->middleware(function ($request, $next) {
            if(!in_array('events',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index(){
        $this->employees = User::allInstallers();
        $this->projects = Project::all();
        $this->schedules = InstallSchedule::all();
        $this->schedule_types = InstallScheduleType::all();

        return view('admin.install-schedules.index', $this->data);
    }


    public function store(StoreSchedule $request){
        $schedule = new InstallSchedule();
        $schedule->schedule_name = $request->schedule_name;
        $schedule->description = $request->description;
        $schedule->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $schedule->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

        if($request->repeat){
            $schedule->repeat = $request->repeat;
        }
        else{
            $schedule->repeat = 'no';
        }

        if ($request->send_reminder) {
            $schedule->send_reminder = $request->send_reminder;
        }
        else {
            $schedule->send_reminder = 'no';
        }

        $schedule->repeat_every = $request->repeat_count;
        $schedule->repeat_cycles = $request->repeat_cycles;
        $schedule->repeat_type = $request->repeat_type;

        $schedule->remind_time = $request->remind_time;
        $schedule->remind_type = $request->remind_type;

        $schedule->type_id = $request->type_id;
        if($request->type_id == 2){
            $schedule->tentative_client = $request->tentative_client;
            $schedule->tentative_city = $request->tentative_city;
            $schedule->tentative_amount = $request->tentative_amount;
            $schedule->project_id = null;
        }
        else{
            if($request->has('project_id'))
                $schedule->project_id = $request->project_id;
        }
        $schedule->status = $request->status;
        $schedule->save();

        if($request->has('project_id') && $request->type_id != 2 && !empty($request->project_id)){
            $project = Project::findorFail($request->project_id);
            $project->install_start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $project->install_end_date = Carbon::parse($request->end_date)->subDay(1)->format('Y-m-d');
            $project->save();
            if(!empty($project->designer) && $request->has('designer_color') && !empty($request->designer_color)){
                $checkExists = DesignerColor::where('user_id', $project->designer->id)->first();
                if (!$checkExists) {
                    DesignerColor::create(['user_id' => $project->designer->id, 'color_code' => $request->input('designer_color')]);
                } else {
                    $checkExists->color_code = $request->input('designer_color');
                    $checkExists->save();
                }
            }
        }

        // Add repeated event
        if ($request->has('repeat') && $request->repeat == 'yes') {
            $repeatCount = $request->repeat_count;
            $repeatType = $request->repeat_type;
            $repeatCycles = $request->repeat_cycles;
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $dueDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $dataF = [];
            $dataS = [];
            for ($i = 1; $i < $repeatCycles; $i++) {
                $repeatStartDate = Carbon::createFromFormat('Y-m-d', $startDate);
                $repeatDueDate = Carbon::createFromFormat('Y-m-d', $dueDate);

                if ($repeatType == 'day') {
                    $repeatStartDate = $repeatStartDate->addDays($repeatCount);
                    $repeatDueDate = $repeatDueDate->addDays($repeatCount);
                } else if ($repeatType == 'week') {
                    $repeatStartDate = $repeatStartDate->addWeeks($repeatCount);
                    $repeatDueDate = $repeatDueDate->addWeeks($repeatCount);
                } else if ($repeatType == 'month') {
                    $repeatStartDate = $repeatStartDate->addMonths($repeatCount);
                    $repeatDueDate = $repeatDueDate->addMonths($repeatCount);
                } else if ($repeatType == 'year') {
                    $repeatStartDate = $repeatStartDate->addYears($repeatCount);
                    $repeatDueDate = $repeatDueDate->addYears($repeatCount);
                }
                $dataF[] = $repeatStartDate;
                $dataS[] = $repeatDueDate;

                $newSchedule = new InstallSchedule();
                $newSchedule->schedule_name = $request->schedule_name;
                $newSchedule->description = $request->description;
                $newSchedule->start_date_time = $repeatStartDate->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
                $newSchedule->end_date_time = $repeatDueDate->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

                if($request->repeat){
                    $newSchedule->repeat = $request->repeat;
                }
                else{
                    $newSchedule->repeat = 'no';
                }

                if ($request->send_reminder) {
                    $newSchedule->send_reminder = $request->send_reminder;
                }
                else {
                    $newSchedule->send_reminder = 'no';
                }

                $newSchedule->repeat_every = $request->repeat_count;
                $newSchedule->repeat_cycles = $request->repeat_cycles;
                $newSchedule->repeat_type = $request->repeat_type;

                $newSchedule->remind_time = $request->remind_time;
                $newSchedule->remind_type = $request->remind_type;

                if($request->type_id == 2){
                    $newSchedule->tentative_client = $request->tentative_client;
                    $newSchedule->tentative_city = $request->tentative_city;
                    $newSchedule->tentative_amount = $request->tentative_amount;
                    $newSchedule->project_id = null;
                }
                else{
                    if($request->has('project_id'))
                        $newSchedule->project_id = $request->project_id;
                }
                $newSchedule->save();

                $startDate = $repeatStartDate->format('Y-m-d');
                $dueDate = $repeatDueDate->format('Y-m-d');
            }
        }

        if($request->has('user_id') && $request->user_id && $request->type_id != 2){
            ProjectInstaller::where('project_id', $schedule->project_id)->delete();
            foreach ($request->user_id as $user_id){
                InstallScheduleAttendee::firstOrCreate(['user_id' => $user_id, 'schedule_id' => $schedule->id]);
                ProjectInstaller::create(['user_id' => $user_id, 'project_id' => $schedule->project_id]);
            }
        }

        return Reply::successWithData(__('messages.scheduleCreateSuccess'),
            ['id' =>$schedule->id,
            'start_date_time' => Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s'),
            'end_date_time' => Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s'),
            'title' => ucfirst($schedule->schedule_name),
            'type' => $schedule->type_id ?? 0,
            'label_color' => $schedule->project->designer->color->color_code ?? ""]);
    }

    public function edit($id){
        $this->employees = User::allInstallers();
        $this->projects = Project::all();
        $this->schedule = InstallSchedule::findOrFail($id);
        $this->schedule_types = InstallScheduleType::all();

        return view('admin.install-schedules.edit', $this->data);
    }

    public function update(UpdateSchedule $request, $id){
        $schedule = InstallSchedule::findOrFail($id);
        $schedule->schedule_name = $request->schedule_name;
        $schedule->description = $request->description;
        $schedule->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $schedule->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

        if($request->repeat){
            $schedule->repeat = $request->repeat;
        }
        else{
            $schedule->repeat = 'no';
        }

        if ($request->send_reminder) {
            $schedule->send_reminder = $request->send_reminder;
        }
        else {
            $schedule->send_reminder = 'no';
        }

        $schedule->repeat_every = $request->repeat_count;
        $schedule->repeat_cycles = $request->repeat_cycles;
        $schedule->repeat_type = $request->repeat_type;

        $schedule->remind_time = $request->remind_time;
        $schedule->remind_type = $request->remind_type;

        $schedule->type_id = $request->type_id;
        if($request->type_id == 2){
            $schedule->tentative_client = $request->tentative_client;
            $schedule->tentative_city = $request->tentative_city;
            $schedule->tentative_amount = $request->tentative_amount;
            $schedule->project_id = null;

        }
        else{
            if($request->has('project_id'))
                $schedule->project_id = $request->project_id;
        }
        $schedule->status = $request->status;

        $schedule->save();

        if($request->type_id == 2)
            InstallScheduleAttendee::where('schedule_id', $schedule->id)->delete();

        if($request->has('project_id') && $request->type_id != 2 && !empty($request->project_id)){
            $project = Project::findorFail($request->project_id);
            $project->install_start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $project->install_end_date = Carbon::parse($request->end_date)->subDay(1)->format('Y-m-d');
            $project->save();
            if(!empty($project->designer) && $request->has('designer_color') && !empty($request->designer_color)){
                $checkExists = DesignerColor::where('user_id', $project->designer->id)->first();
                if (!$checkExists) {
                    DesignerColor::create(['user_id' => $project->designer->id, 'color_code' => $request->input('designer_color')]);
                } else {
                    $checkExists->color_code = $request->input('designer_color');
                    $checkExists->save();
                }
            }
        }

        if(!empty($request->user_id) && $request->type_id != 2){
            foreach ($request->user_id as $user_id) {
                $checkExists = InstallScheduleAttendee::where('schedule_id', $schedule->id)->where('user_id', $user_id)->first();
                $checkProjectExists = ProjectInstaller::where('project_id', $schedule->project_id)->where('user_id', $user_id)->first();
                if (!$checkExists) {
                    InstallScheduleAttendee::create(['user_id' => $user_id, 'schedule_id' => $schedule->id]);
                }
                if (!$checkProjectExists) {
                    ProjectInstaller::create(['user_id' => $user_id, 'project_id' => $schedule->project_id]);
                }
            }
        }

        return Reply::successWithData(__('messages.scheduleUpdateSuccess'),
            ['id' =>$schedule->id,
            'start_date_time' => Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s'),
            'end_date_time' => Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s'),
            'title' => ucfirst($schedule->schedule_name),
            'type' => $schedule->type_id ?? 0,
            'label_color' => $schedule->project->designer->color->color_code ?? ""]);
    }

    public function moveEvent(MoveSchedule $request, $id){
        $schedule = InstallSchedule::findOrFail($id);
        $schedule->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $schedule->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');
        $schedule->save();
        if(!empty($schedule->project_id)){
            $project = Project::findOrFail($schedule->project_id);
            if($project){
                $project->install_start_date = Carbon::parse($request->start_date)->format('Y-m-d');
                $project->install_end_date = Carbon::parse($request->end_date)->subDay(1)->format('Y-m-d');
                $project->save();
            }
        }

        return Reply::success(__('messages.scheduleUpdateSuccess'));
    }

    public function createPDF($id){
        $this->event = InstallSchedule::findOrFail($id);
        if($this->event){
            if($this->event->event_type === 1){
                $this->lead = Lead::findorFail($this->event->lead_id);
                $this->designer = User::findorFail($this->event->attendee->user_id);
                $this->notes = Note::allLeadNotes($this->event->lead_id);
                if(!empty($this->lead->interest_areas)){
                    $areas = explode(",", $this->lead->interest_areas);
                    $this->interestAreas = InterestArea::whereIn('id', $areas)->get();
                }
                else{
                    $this->interestAreas = [];
                }
                $pdf = app('dompdf.wrapper');
                $pdf->loadView('admin.event-calendar.event-lead-pdf', $this->data);
                return $pdf->download('Appointment-'. $this->event->schedule_name . '.pdf');
            }
            else if($this->event->event_type === 2){
                if(!is_null($this->event->lead_id)){
                    $this->lead = Lead::findorFail($this->event->lead_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $this->notes = Note::allLeadNotes($this->event->lead_id);
                    if(!empty($this->lead->interest_areas)){
                        $areas = explode(",", $this->lead->interest_areas);
                        $this->interestAreas = InterestArea::whereIn('id', $areas)->get();
                    }
                    else{
                        $this->interestAreas = [];
                    }
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-lead-pdf', $this->data);
                }
                else if(!is_null($this->event->project_id)){
                    $this->project = Project::findorFail($this->event->project_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-project-pdf', $this->data);
                }
                else if(!is_null($this->event->client_id)){
                    $this->client = Client::findorFail($this->event->client_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-client-pdf', $this->data);
                }

                return $pdf->download('Appointment-'. $this->event->schedule_name . '.pdf');
            }
        }
        else{
            return Redirect::back();
        }
    }

    public function sendEmail($id){
        $this->event = InstallSchedule::findOrFail($id);
        if($this->event){
            if($this->event->event_type === 1){
                $this->lead = Lead::findorFail($this->event->lead_id);
                $this->designer = User::findorFail($this->event->attendee->user_id);
                $this->notes = Note::allLeadNotes($this->event->lead_id);
                if(!empty($this->lead->interest_areas)){
                    $areas = explode(",", $this->lead->interest_areas);
                    $this->interestAreas = InterestArea::whereIn('id', $areas)->get();
                }
                else{
                    $this->interestAreas = [];
                }
                $pdf = app('dompdf.wrapper');
                $pdf->loadView('admin.event-calendar.event-lead-pdf', $this->data);
                $filename = 'Appointment-'. $this->event->schedule_name . '.pdf';
                $pdf->save(public_path('/pdf/'.$filename));
                $this->designer->notify(new EventEmail($this->event, $filename));
            }
            else if($this->event->event_type === 2){
                if(!is_null($this->event->lead_id)){
                    $this->lead = Lead::findorFail($this->event->lead_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $this->notes = Note::allLeadNotes($this->event->lead_id);
                    if(!empty($this->lead->interest_areas)){
                        $areas = explode(",", $this->lead->interest_areas);
                        $this->interestAreas = InterestArea::whereIn('id', $areas)->get();
                    }
                    else{
                        $this->interestAreas = [];
                    }
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-lead-pdf', $this->data);

                }
                else if(!is_null($this->event->project_id)){
                    $this->project = Project::findorFail($this->event->project_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-project-pdf', $this->data);
                }
                else if(!is_null($this->event->client_id)){
                    $this->client = Client::findorFail($this->event->client_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('admin.event-calendar.event-client-pdf', $this->data);
                }
                $filename = 'Appointment-'. $this->event->schedule_name . '.pdf';
                $pdf->save(public_path('/pdf/'.$filename));
                $this->designer->notify(new EventEmail($this->event, $filename));
            }
            if(File::exists(public_path('/pdf/'.$filename))){
                File::delete(public_path('/pdf/'.$filename));
            }
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else{
            return Reply::error(__('messages.emailSendError'));
        }
    }

    public function show($id){
        $this->event = InstallSchedule::findOrFail($id);
        return view('admin.event-calendar.show', $this->data);
    }

    public function removeAttendee(Request $request){
        $attendee = InstallScheduleAttendee::find($request->attendeeId);
        if($attendee){
            ProjectInstaller::where('project_id', $request->projectId)->where('user_id', $attendee->user_id)->delete();
            $attendee->delete();
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function destroy($id){
        InstallSchedule::destroy($id);
        InstallScheduleAttendee::where('schedule_id', $id)->delete();
        return Reply::success(__('messages.scheduleDeleteSuccess'));
    }
}
