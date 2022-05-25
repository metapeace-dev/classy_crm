<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Event;
use App\EventAttendee;
use App\EventStatus;
use App\Helper\Reply;
use App\Http\Requests\Events\MoveEvent;
use App\Http\Requests\Events\StoreEvent;
use App\Http\Requests\Events\UpdateEvent;
use App\InterestArea;
use App\Lead;
use App\Note;
use App\Notifications\EventEmail;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class AdminEventCalendarController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.appointments');
        $this->pageIcon = 'icon-calender';
        $this->middleware(function ($request, $next) {
            if(!in_array('events',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index(){
        $this->designers = User::allDesigners();
        $this->projects = Project::all();
        $this->leads = Lead::all();
        $this->clients = Client::all();
        $this->events = Event::all();
        $this->event_status = EventStatus::all();

        if(Session::has('appointmentDate')){
            $this->curDate = Session::get('appointmentDate');
            Session::forget('appointmentDate');
        }
        return view('admin.event-calendar.index', $this->data);
    }

    public function leadAppt($id){
        $this->designers = User::allDesigners();
        $this->events = Event::all();
        $this->lead = Lead::findOrFail($id);
        $this->leads = Lead::all();
        $this->projects = Project::all();
        $this->clients = Client::all();
        $this->event_status = EventStatus::all();

        $this->apptName = '';
        if(isset($this->lead->client_id) && !empty($this->lead->client_id)){
            if(!empty($this->lead->client->last_name)){
                $this->apptName .= ucfirst($this->lead->client->last_name);
            }

            if(!empty($this->lead->client->first_name)){
                $this->apptName .= ' '.ucfirst($this->lead->client->first_name);
            }

            if(!empty($this->lead->client->city)){
                $this->apptName .= ', '.$this->lead->client->city;
            }

            if(!empty($this->lead->client->phone_number)){
                $this->apptName .= ' and '.$this->lead->client->phone_number;
            }
        }
        else{
            if(!empty($this->lead->last_name)){
                $this->apptName .= ucfirst($this->lead->last_name);
            }

            if(!empty($this->lead->first_name)){
                $this->apptName .= ' '.ucfirst($this->lead->first_name);
            }

            if(!empty($this->lead->city)){
                $this->apptName .= ', '.$this->lead->city;
            }

            if(!empty($this->lead->phone)){
                $this->apptName .= ' and '.$this->lead->phone;
            }
        }

        return view('admin.event-calendar.set', $this->data);
    }

    public function projectAppt($id){
        $this->designers = User::allDesigners();
        $this->events = Event::all();
        $this->project = Project::findOrFail($id);
        $this->leads = Lead::all();
        $this->projects = Project::all();
        $this->clients = Client::all();
        $this->event_status = EventStatus::all();

        $this->apptName = '';
        if(isset($this->project->client_id) && !empty($this->project->client_id)){
            if(!empty($this->project->client->last_name)){
                $this->apptName .= ucfirst($this->project->client->last_name);
            }

            if(!empty($this->project->client->first_name)){
                $this->apptName .= ' '.ucfirst($this->project->client->first_name);
            }

            if(!empty($this->project->client->city)){
                $this->apptName .= ', '.$this->project->client->city;
            }

            if(!empty($this->project->client->phone_number)){
                $this->apptName .= ' and '.$this->project->client->phone_number;
            }
        }
        else{
            if(!empty($this->project->project_name)){
                $this->apptName .= $this->project->project_name;
            }

            if(!empty($this->project->city)){
                $this->apptName .= ', '.$this->project->city;
            }

            if(!empty($this->project->phone)){
                $this->apptName .= ' and '.$this->project->phone;
            }
        }
        return view('admin.event-calendar.set', $this->data);
    }

    public function clientAppt($id){
        $this->designers = User::allDesigners();
        $this->events = Event::all();
        $this->client = Client::findOrFail($id);
        $this->leads = Lead::all();
        $this->projects = Project::all();
        $this->clients = Client::all();
        $this->event_status = EventStatus::all();

        $this->apptName = '';
        if(!empty($this->client->last_name)){
            $this->apptName .= ucfirst($this->client->last_name);
        }

        if(!empty($this->client->first_name)){
            $this->apptName .= ' '.ucfirst($this->client->first_name);
        }

        if(!empty($this->client->city)){
            $this->apptName .= ', '.$this->client->city;
        }

        if(!empty($this->client->phone_number)){
            $this->apptName .= ' and '.$this->client->phone_number;
        }

        return view('admin.event-calendar.set', $this->data);
    }

    public function store(StoreEvent $request){
        $event = new Event();
        $event->event_name = $request->event_name;
        if($request->event_type == 3){
            $event->event_name = 'Blocked Time';
        }

        if($request->event_type == 4){
            $event->event_name = 'Personal Time Off';
        }

        $event->description = $request->description;
        $event->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $event->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

        if($request->repeat){
            $event->repeat = $request->repeat;
        }
        else{
            $event->repeat = 'no';
        }

        if ($request->send_reminder) {
            $event->send_reminder = $request->send_reminder;
        }
        else {
            $event->send_reminder = 'no';
        }

        $event->repeat_every = $request->repeat_count;
        $event->repeat_cycles = $request->repeat_cycles;
        $event->repeat_type = $request->repeat_type;

        $event->remind_time = $request->remind_time;
        $event->remind_type = $request->remind_type;

        $event->label_color = $request->label_color;
        if($request->has('status_id')){
            $event->status_id = $request->status_id;
        }
        else{
            $event_status = EventStatus::where('name', '=', 'scheduled')->first();
            $event->status_id = $event_status->id;
        }
        if($request->has('project_id'))
            $event->project_id = $request->project_id;
        if($request->has('lead_id'))
            $event->lead_id = $request->lead_id;
        if($request->has('client_id'))
            $event->client_id = $request->client_id;
        $event->event_type = $request->event_type;
        $event->save();

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

                $event = new Event();
                $event->event_name = $request->event_name;
                $event->where = $request->where;
                $event->description = $request->description;
                $event->start_date_time = $repeatStartDate->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
                $event->end_date_time = $repeatDueDate->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

                if($request->repeat){
                    $event->repeat = $request->repeat;
                }
                else{
                    $event->repeat = 'no';
                }

                if ($request->send_reminder) {
                    $event->send_reminder = $request->send_reminder;
                }
                else {
                    $event->send_reminder = 'no';
                }

                $event->repeat_every = $request->repeat_count;
                $event->repeat_cycles = $request->repeat_cycles;
                $event->repeat_type = $request->repeat_type;

                $event->remind_time = $request->remind_time;
                $event->remind_type = $request->remind_type;

                $event->label_color = $request->label_color;
                if($request->has('project_id'))
                    $event->project_id = $request->project_id;
                if($request->has('lead_id'))
                    $event->lead_id = $request->lead_id;
                if($request->has('client_id'))
                    $event->client_id = $request->client_id;
                $event->save();

                $startDate = $repeatStartDate->format('Y-m-d');
                $dueDate = $repeatDueDate->format('Y-m-d');
            }
        }

        if($request->has('user_id') && $request->user_id){
            EventAttendee::firstOrCreate(['user_id' => $request->user_id, 'event_id' => $event->id]);
            if($request->lead_id){
                $lead = Lead::findorFail($request->lead_id);
                $lead->user_id = $request->user_id;
                $lead->save();
            }
            else if($request->project_id){
                $project = Project::findorFail($request->project_id);
                $project->user_id = $request->user_id;
                $project->save();
            }
        }

        if($request->has('storeDate')){
            $dateStore = Carbon::parse($request->start_date)->format('Y-m-d');
            Session::put('appointmentDate', $dateStore);
        }


        return Reply::successWithData(__('messages.eventCreateSuccess'),
            ['id' =>$event->id,
            'start_date_time' => Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s'),
            'end_date_time' => Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s'),
            'event_type' => $event->event_type,
            'resource_id' => $request->user_id,
            'title' => $event->event_name,
            'label_color' => $event->label_color]);
    }

    public function edit($id){
        $this->designers = User::allDesigners();
        $this->projects = Project::all();
        $this->leads = Lead::all();
        $this->clients = Client::all();
        $this->event = Event::findOrFail($id);
        $this->event_status = EventStatus::all();
        return view('admin.event-calendar.edit', $this->data);
    }

    public function update(UpdateEvent $request, $id){
        $event = Event::findOrFail($id);
        $event->event_name = $request->event_name;
        if($request->event_type == 3){
            $event->event_name = 'Blocked Time';
        }

        if($request->event_type == 4){
            $event->event_name = 'Personal Time Off';
        }
        $event->description = $request->description;
        $event->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $event->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');

        if($request->repeat){
            $event->repeat = $request->repeat;
        }
        else{
            $event->repeat = 'no';
        }

        if ($request->send_reminder) {
            $event->send_reminder = $request->send_reminder;
        }
        else {
            $event->send_reminder = 'no';
        }

        $event->repeat_every = $request->repeat_count;
        $event->repeat_cycles = $request->repeat_cycles;
        $event->repeat_type = $request->repeat_type;

        $event->remind_time = $request->remind_time;
        $event->remind_type = $request->remind_type;

        $event->label_color = $request->label_color;
        $event->status_id = $request->status_id;
        if($request->has('project_id'))
            $event->project_id = $request->project_id;
        if($request->has('lead_id'))
            $event->lead_id = $request->lead_id;
        if($request->has('client_id'))
            $event->client_id = $request->client_id;
        $event->event_type = $request->event_type;
        $event->save();

        if($request->user_id){
            $checkExists = EventAttendee::where('event_id', $event->id)->first();
            if(!$checkExists){
                EventAttendee::create(['user_id' => $request->user_id, 'event_id' => $event->id]);
            }
            else {
                $checkExists->user_id = $request->user_id;
                $checkExists->save();
            }
            if($request->lead_id){
                $lead = Lead::findorFail($request->lead_id);
                $lead->user_id = $request->user_id;
                $lead->save();
            }
            else if($request->project_id){
                $project = Project::findorFail($request->project_id);
                $project->user_id = $request->user_id;
                $project->save();
            }
        }

        return Reply::successWithData(__('messages.eventUpdateSuccess'),
            ['id' =>$event->id,
            'start_date_time' => Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s'),
            'end_date_time' => Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s'),
            'event_type' => $event->event_type,
            'resource_id' => $request->user_id,
            'title' => $event->event_name,
            'label_color' => $event->label_color]);
    }

    public function moveEvent(MoveEvent $request, $id){
        $event = Event::findOrFail($id);
        $event->start_date_time = Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s');
        $event->end_date_time = Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s');
        $event->save();
        if($request->user_id){
            $checkExists = EventAttendee::where('event_id', $event->id)->first();
            if(!$checkExists){
                EventAttendee::create(['user_id' => $request->user_id, 'event_id' => $event->id]);
            }
            else {
                $checkExists->user_id = $request->user_id;
                $checkExists->save();
            }
        }
        return Reply::success(__('messages.eventUpdateSuccess'));
    }

    public function createPDF($id){
        $this->event = Event::findOrFail($id);
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
                return $pdf->download('Appointment-'. $this->event->event_name . '.pdf');
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

                return $pdf->download('Appointment-'. $this->event->event_name . '.pdf');
            }
        }
        else{
            return Redirect::back();
        }
    }

    public function sendEmail($id){
        $this->event = Event::findOrFail($id);
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
                $filename = 'Appointment-'. $this->event->event_name . '.pdf';
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
                $filename = 'Appointment-'. $this->event->event_name . '.pdf';
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
        $this->event = Event::findOrFail($id);
        return view('admin.event-calendar.show', $this->data);
    }

    public function removeAttendee(Request $request){
        EventAttendee::destroy($request->attendeeId);
        return Reply::dataOnly(['status' => 'success']);
    }

    public function destroy($id){
        Event::destroy($id);
        EventAttendee::where('event_id', $id)->delete();
        return Reply::success(__('messages.eventDeleteSuccess'));
    }
}
