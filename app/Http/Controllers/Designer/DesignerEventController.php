<?php

namespace App\Http\Controllers\Designer;

use App\Client;
use App\Event;
use App\EventAttendee;
use App\Helper\Reply;
use App\Http\Requests\Events\MoveSchedule;
use App\Http\Requests\Events\StoreSchedule;
use App\Http\Requests\Events\UpdateSchedule;
use App\InterestArea;
use App\Lead;
use App\Note;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DesignerEventController extends DesignerBaseController
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
        $this->designer = $this->user;
        $this->projects = Project::all();
        $this->events = Event::join('event_attendees', 'event_attendees.event_id', '=', 'events.id')->where('event_attendees.user_id', $this->user->id)
                        ->select('events.*')->get();

        return view('designer.event-calendar.index', $this->data);
    }

    public function store(StoreSchedule $request){
        if($request->event_type == 1 || $request->event_type == 2){
            abort(403);
        }
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
        $event->project_id = $request->project_id;
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
                $event->save();

                $startDate = $repeatStartDate->format('Y-m-d');
                $dueDate = $repeatDueDate->format('Y-m-d');
            }
        }

        EventAttendee::firstOrCreate(['user_id' => $this->user->id, 'event_id' => $event->id]);

        return Reply::successWithData(__('messages.eventCreateSuccess'),
            ['id' =>$event->id,
                'start_date_time' => Carbon::parse($request->start_date)->format('Y-m-d').' '.Carbon::parse($request->start_time)->format('H:i:s'),
                'end_date_time' => Carbon::parse($request->end_date)->format('Y-m-d').' '.Carbon::parse($request->end_time)->format('H:i:s'),
                'event_type' => $event->event_type,
                'resource_id' => $this->user->id,
                'title' => $event->event_name,
                'label_color' => $event->label_color]);
    }

    public function edit($id){
        $this->event = Event::findOrFail($id);
        $view = view('designer.event-calendar.edit', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(UpdateSchedule $request, $id){
        if($request->event_type == 1 || $request->event_type == 2){
            abort(403);
        }
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
        $event->project_id = $request->project_id;
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

    public function moveEvent(MoveSchedule $request, $id){
        $event = Event::findOrFail($id);
        if($event->event_type == 1 || $event->event_type == 2){
            return Reply::error(__('Permission Error!'));
        }
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
                $pdf->loadView('designer.event-calendar.event-lead-pdf', $this->data);
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
                    $pdf->loadView('designer.event-calendar.event-lead-pdf', $this->data);
                }
                else if(!is_null($this->event->project_id)){
                    $this->project = Project::findorFail($this->event->project_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('designer.event-calendar.event-project-pdf', $this->data);
                }
                else if(!is_null($this->event->client_id)){
                    $this->client = Client::findorFail($this->event->client_id);
                    $this->designer = User::findorFail($this->event->attendee->user_id);
                    $pdf = app('dompdf.wrapper');
                    $pdf->loadView('designer.event-calendar.event-client-pdf', $this->data);
                }

                return $pdf->download('Appointment-'. $this->event->event_name . '.pdf');
            }
        }
        else{
            return Redirect::back();
        }
    }

    public function show($id){
        $this->event = Event::findOrFail($id);
        return view('designer.event-calendar.show', $this->data);
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
