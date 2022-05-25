<?php

namespace App\Http\Controllers\Installer;

use App\Client;
use App\Helper\Reply;
use App\Project;
use App\Lead;
use App\ReportSetting;
use App\User;
use App\EventStatus;
use App\Event;
use App\EventAttendee;
use App\EventType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InstallerEventReportController extends InstallerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.appointmentReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->event_status = EventStatus::all();
        $this->fieldsData = ReportSetting::where('type', 'appointment');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        return view('installer.reports.events.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data($startDate = null, $endDate = null, $type = null) {
        $this->userDetail = auth()->user();

        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $events = Event::leftJoin('event_types', 'event_types.id', 'events.event_type')
                ->leftJoin('event_status', 'event_status.id', 'events.status_id')
                ->leftJoin('leads', 'leads.id', 'events.lead_id')
                ->leftJoin('projects', 'projects.id', 'events.project_id')
                ->join('event_attendees', 'event_attendees.event_id', '=', 'events.id')
                ->select('events.id', 'events.event_name','event_types.type as event_type', 'event_status.name as status', 'events.status_id', 'events.start_date_time', 'events.end_date_time', 'events.created_at', 'event_attendees.user_id as user_id', 'events.project_id', 'events.lead_id', 'events.client_id', 'events.description');


        if (!is_null($startDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '<=', $endDate);
        }

        if (!is_null($type) && $type !== 'all') {
            $events->where('events.status_id', '=', $type);
        }

        $events->where('event_attendees.user_id', $this->userDetail->id);
        $events->whereIn('events.event_type', [1,2]);
        $events->orderBy('events.created_at', 'DESC');
        $events = $events->get();

        $removeData = ['project_id', 'client_id', 'user_id', 'status_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'appointment');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        foreach ($fieldsData as $field){
            if($field->status == 'deactive'){
                $removeData[] = $field->field_name;
            }
        }

        return DataTables::of($events)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'scheduled') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->editColumn('start_date_time', function ($row) {
                return $row->start_date_time->format($this->global->date_format. ' g:i:s A');
            })
            ->editColumn('end_date_time', function ($row) {
                return $row->end_date_time->format($this->global->date_format. ' g:i:s A');
            })
            ->addColumn('client', function($row){
                if($row->lead_id){
                    $lead = Lead::find($row->lead_id);
                    return $lead->client->full_name ?? '';
                }
                elseif ($row->project_id){
                    $project = Project::find($row->project_id);
                    return $project->client->full_name ?? '';
                }
                elseif($row->client_id){
                    $client = Client::find($row->client_id);
                    return $client->full_name ?? '';
                }
                return '';
            })
            ->rawColumns(['created_at', 'status', 'start_date_time', 'end_date_time'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export($startDate = null, $endDate = null, $type=null) {
        $this->userDetail = auth()->user();
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $events = Event::leftJoin('event_types', 'event_types.id', 'events.event_type')
            ->leftJoin('event_status', 'event_status.id', 'events.status_id')
            ->leftJoin('leads', 'leads.id', 'events.lead_id')
            ->leftJoin('projects', 'projects.id', 'events.project_id')
            ->join('event_attendees', 'event_attendees.event_id', '=', 'events.id')
            ->select('events.id', 'events.event_name','event_types.type as event_type', 'event_status.name as status', 'events.status_id', 'events.start_date_time', 'events.end_date_time', 'events.created_at', 'event_attendees.user_id as user_id', 'events.project_id', 'events.lead_id', 'events.client_id', 'events.description');


        if (!is_null($startDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '<=', $endDate);
        }

        if (!is_null($type) && $type !== 'all') {
            $events->where('events.status_id', '=', $type);
        }
        $events->where('event_attendees.user_id', $this->userDetail->id);
        $events->whereIn('events.event_type', [1,2]);
        $events->orderBy('events.created_at', 'DESC');

        $title = 'Appointment Report';
        $attributes = ['status_id'];

        $events = $events->get()->makeHidden($attributes);

        $removeData = ['project_id', 'client_id', 'user_id', 'status_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'appointment');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        $exportArray[0] = ['ID'];
        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }
        $exportArray[0][] = 'Created On';

        foreach ($events as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'designer'){
                    $user = User::find($row->user_id);
                    $data['designer'] = $user->name ?? '';
                }
                else if($active == 'client'){
                    if($row->lead_id){
                        $lead = Lead::find($row->lead_id);
                        $data['client'] =  $lead->client->full_name ?? '';
                    }
                    elseif ($row->project_id){
                        $project = Project::find($row->project_id);
                        $data['client'] =  $project->client->full_name ?? '';
                    }
                    elseif($row->client_id){
                        $client = Client::find($row->client_id);
                        $data['client'] =  $client->full_name ?? '';
                    }
                    else{
                        $data['client'] = '';
                    }
                }
                else if($active == 'start_date_time'){
                    $data['start_date_time'] = $row->start_date_time->format($this->global->date_format. ' g:i:s A');
                }
                else if($active == 'end_date_time'){
                    $data['end_date_time'] = $row->end_date_time->format($this->global->date_format. ' g:i:s A');
                }
                else{
                    $data[$active] = $row->{$active};
                }
            }
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Appointment Report file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));

                });

            });

        })->download('xlsx');
    }

}
