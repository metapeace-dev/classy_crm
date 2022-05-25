<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Event;
use App\EventStatus;
use App\Helper\Reply;
use App\InstallSchedule;
use App\InstallScheduleType;
use App\Lead;
use App\Notifications\NewReportEmail;
use App\Project;
use App\ReportSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InstallScheduleReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.installScheduleReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->installers = User::allInstallers();
        $this->schedule_types = InstallScheduleType::all();
        $this->statuses = InstallSchedule::getEnumColumnValues('install_schedules', 'status');
        $this->fieldsData = ReportSetting::where('type', 'install_schedule');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        return view('admin.reports.install-schedules.index', $this->data);
    }

    public function store(Request $request){
    }

    public function data(Request $request) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();

        $installSchedules = InstallSchedule::leftJoin('install_schedule_types', 'install_schedule_types.id', 'install_schedules.type_id')
                ->leftJoin('install_schedule_attendees', 'install_schedule_attendees.schedule_id', 'install_schedules.id')
                ->leftJoin('projects', 'projects.id', 'install_schedules.project_id')
                ->leftJoin('users as designers', 'designers.id', 'projects.user_id')
                ->leftJoin('clients', 'clients.id', 'projects.client_id')
                ->select('install_schedules.id', 'install_schedules.schedule_name', 'install_schedules.description', 'install_schedules.start_date_time', 'install_schedules.end_date_time',
                    'install_schedules.tentative_client', 'install_schedules.tentative_city', 'install_schedules.tentative_amount', 'install_schedules.status',
                    'install_schedules.created_at', 'install_schedule_types.type as schedule_type', 'projects.project_name as project', 'projects.city as city', 'projects.sales_price as amount', 'designers.name as designer', 'clients.first_name as client_first_name',
                    'clients.last_name as client_last_name');


        if (!is_null($startDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '<=', $endDate);
        }

        if ($request->has('designer_id') && !empty($request->get('designer_id'))) {
            $installSchedules->where('projects.user_id', '=', $request->get('designer_id'));
        }

        if ($request->has('installer_id') && !empty($request->get('installer_id'))) {
            $installSchedules->where('install_schedule_attendees.user_id', '=', $request->get('installer_id'));
        }

        if ($request->has('status') && !empty($request->get('status'))) {
            $installSchedules->where('install_schedules.status', '=', $request->get('status'));
        }

        if ($request->has('type_id') && !empty($request->get('type_id'))) {
            $installSchedules->where('install_schedules.type_id', '=', $request->get('type_id'));
        }

        $installSchedules->orderBy('install_schedules.created_at', 'DESC');
        $installSchedules = $installSchedules->get();

        $removeData = ['tentative_client', 'tentative_city', 'tentative_amount'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'install_schedules');

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

        return DataTables::of($installSchedules)
            ->addColumn('installer', function ($row){
                $members = '';
                if(count($row->attendees) > 0){
                    foreach ($row->attendees as $attendee) {
                        $members .= ($attendee->user->image) ? '<img data-toggle="tooltip" data-original-title="' . ucwords($attendee->user->name) . '" src="' . asset_url('avatar/' . $attendee->user->image) . '"
                        alt="user" class="img-circle" width="30"> ' : '<img data-toggle="tooltip" data-original-title="' . ucwords($attendee->user->name) . '" src="' . asset('img/default-profile-2.png') . '"
                        alt="user" class="img-circle" width="30"> ';

                    }
                    return $members;
                }
                else{
                    return '';
                }
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'incomplete') {
                    $status = '<label class="label label-info">' . ucfirst($row->status) . '</label>';
                } else if ($row->status == 'complete') {
                    $status = '<label class="label label-success">' . ucfirst($row->status) . '</label>';
                } else {
                    $status = '<label class="label label-info">' . ucfirst($row->status) . '</label>';
                }
                return $status;
            })
            ->editColumn('schedule_type', function ($row){
                return ucfirst($row->schedule_type ?? '');
            })
            ->editColumn('start_date_time', function ($row) {
                return $row->start_date_time->format($this->global->date_format. ' g:i:s A');
            })
            ->editColumn('end_date_time', function ($row) {
                return $row->end_date_time->format($this->global->date_format. ' g:i:s A');
            })
            ->editColumn('client', function ($row){
                if(!empty($row->project)){
                    return ucfirst($row->client_first_name ?? ''). ' ' . ucfirst($row->client_last_name ?? '');
                }
                else{
                    return ucfirst($row->tentative_client ?? '') ;
                }
            })
            ->editColumn('city', function ($row){
                if(!empty($row->project)){
                    return $row->city ?? '';
                }
                else{
                    return $row->tentative_city ?? '';
                }
            })
            ->editColumn('amount', function ($row){
                if(!empty($row->project)){
                    return '$'. number_format($row->amount ?? 0, 2, '.', ',');
                }
                else{
                    return '$'. number_format($row->tentative_amount ?? 0, 2, '.', ',');
                }
            })
            ->rawColumns(['created_at', 'status', 'start_date_time', 'end_date_time','installer', 'action'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export(Request $request) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();

        $installSchedules = InstallSchedule::leftJoin('install_schedule_types', 'install_schedule_types.id', 'install_schedules.type_id')
            ->leftJoin('install_schedule_attendees', 'install_schedule_attendees.schedule_id', 'install_schedules.id')
            ->leftJoin('projects', 'projects.id', 'install_schedules.project_id')
            ->leftJoin('users as designers', 'designers.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('install_schedules.id', 'install_schedules.schedule_name', 'install_schedules.description', 'install_schedules.start_date_time', 'install_schedules.end_date_time',
                'install_schedules.tentative_client', 'install_schedules.tentative_city', 'install_schedules.tentative_amount', 'install_schedules.status',
                'install_schedules.created_at', 'install_schedule_types.type as schedule_type', 'projects.project_name as project', 'projects.city as city', 'projects.sales_price as amount', 'designers.name as designer', 'clients.first_name as client_first_name',
                'clients.last_name as client_last_name');


        if (!is_null($startDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '<=', $endDate);
        }

        if ($request->has('designer_id') && !empty($request->get('designer_id'))) {
            $installSchedules->where('projects.user_id', '=', $request->get('designer_id'));
        }

        if ($request->has('installer_id') && !empty($request->get('installer_id'))) {
            $installSchedules->where('install_schedule_attendees.user_id', '=', $request->get('installer_id'));
        }

        if ($request->has('status') && !empty($request->get('status'))) {
            $installSchedules->where('install_schedules.status', '=', $request->get('status'));
        }

        if ($request->has('type_id') && !empty($request->get('type_id'))) {
            $installSchedules->where('install_schedules.type_id', '=', $request->get('type_id'));
        }

        $installSchedules->orderBy('install_schedules.created_at', 'DESC');
        $installSchedules = $installSchedules->get();

        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'install_schedule');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        $title = 'Install Schedule Report';

        $exportArray[0] = ['ID'];
        $activeData = [];
        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }
        $exportArray[0][] = 'Created On';

        foreach ($installSchedules as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'status'){
                    $data[$active] = ucfirst($row->status ?? '');
                }
                else if($active == 'schedule_type'){
                    $data[$active] = ucfirst($row->schedule_type ?? '');
                }
                else if($active == 'installer'){
                    $members = '';
                    if(count($row->attendees) > 0){
                        foreach ($row->attendees as $attendee) {
                            if(empty($members))
                                $members .= ucwords($attendee->user->name);
                            else
                                $members .= ', '.ucwords($attendee->user->name);

                        }
                        $data[$active] = $members;
                    }
                    else{
                        $data[$active] = '';
                    }
                }
                else if($active == 'client'){
                    if(!empty($row->project)){
                        $data[$active] = ucfirst($row->client_first_name ?? ''). ' ' . ucfirst($row->client_last_name ?? '');
                    }
                    else{
                        $data[$active] = ucfirst($row->tentative_client ?? '') ;
                    }
                }
                else if($active == 'city'){
                    if(!empty($row->project)){
                        $data[$active] =  $row->city ?? '';
                    }
                    else{
                        $data[$active] =  $row->tentative_city ?? '';
                    }
                }
                else if($active == 'amount'){
                    if(!empty($row->project)){
                        $data[$active] = '$'. number_format($row->amount ?? 0, 2, '.', ',');
                    }
                    else{
                        $data[$active] = '$'. number_format($row->tentative_amount ?? 0, 2, '.', ',');
                    }
                }
                else if($active == 'start_date_time'){
                    $data[$active] = $row->start_date_time->format($this->global->date_format. ' g:i:s A');
                }
                else if($active == 'end_date_time'){
                    $data[$active] = $row->end_date_time->format($this->global->date_format. ' g:i:s A');
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
            $excel->setDescription('Install Schedule Report file');

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

    public function exportPDF(Request $request) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();

        $installSchedules = InstallSchedule::leftJoin('install_schedule_types', 'install_schedule_types.id', 'install_schedules.type_id')
            ->leftJoin('install_schedule_attendees', 'install_schedule_attendees.schedule_id', 'install_schedules.id')
            ->leftJoin('projects', 'projects.id', 'install_schedules.project_id')
            ->leftJoin('users as designers', 'designers.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('install_schedules.id', 'install_schedules.schedule_name', 'install_schedules.description', 'install_schedules.start_date_time', 'install_schedules.end_date_time',
                'install_schedules.tentative_client', 'install_schedules.tentative_city', 'install_schedules.tentative_amount', 'install_schedules.status',
                'install_schedules.created_at', 'install_schedule_types.type as schedule_type', 'projects.project_name as project', 'projects.city as city', 'projects.sales_price as amount', 'designers.name as designer', 'clients.first_name as client_first_name',
                'clients.last_name as client_last_name');


        if (!is_null($startDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $installSchedules->where(DB::raw('DATE(install_schedules.`created_at`)'), '<=', $endDate);
        }

        if ($request->has('designer_id') && !empty($request->get('designer_id'))) {
            $installSchedules->where('projects.user_id', '=', $request->get('designer_id'));
        }

        if ($request->has('installer_id') && !empty($request->get('installer_id'))) {
            $installSchedules->where('install_schedule_attendees.user_id', '=', $request->get('installer_id'));
        }

        if ($request->has('status') && !empty($request->get('status'))) {
            $installSchedules->where('install_schedules.status', '=', $request->get('status'));
        }

        if ($request->has('type_id') && !empty($request->get('type_id'))) {
            $installSchedules->where('install_schedules.type_id', '=', $request->get('type_id'));
        }

        $installSchedules->orderBy('install_schedules.created_at', 'DESC');
        $installSchedules = $installSchedules->get();

        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'install_schedule');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        $title = 'Install Schedule Report';

        $exportArray[0] = ['ID'];
        $activeData = [];
        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }
        $exportArray[0][] = 'Created On';

        foreach ($installSchedules as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'status'){
                    $data[$active] = ucfirst($row->status ?? '');
                }
                else if($active == 'schedule_type'){
                    $data[$active] = ucfirst($row->schedule_type ?? '');
                }
                else if($active == 'installer'){
                    $members = '';
                    if(count($row->attendees) > 0){
                        foreach ($row->attendees as $attendee) {
                            if(empty($members))
                                $members .= ucwords($attendee->user->name);
                            else
                                $members .= ', '.ucwords($attendee->user->name);

                        }
                        $data[$active] = $members;
                    }
                    else{
                        $data[$active] = '';
                    }
                }
                else if($active == 'client'){
                    if(!empty($row->project)){
                        $data[$active] = ucfirst($row->client_first_name ?? ''). ' ' . ucfirst($row->client_last_name ?? '');
                    }
                    else{
                        $data[$active] = ucfirst($row->tentative_client ?? '') ;
                    }
                }
                else if($active == 'city'){
                    if(!empty($row->project)){
                        $data[$active] =  $row->city ?? '';
                    }
                    else{
                        $data[$active] =  $row->tentative_city ?? '';
                    }
                }
                else if($active == 'amount'){
                    if(!empty($row->project)){
                        $data[$active] = '$'. number_format($row->amount ?? 0, 2, '.', ',');
                    }
                    else{
                        $data[$active] = '$'. number_format($row->tentative_amount ?? 0, 2, '.', ',');
                    }
                }
                else if($active == 'start_date_time'){
                    $data[$active] = $row->start_date_time->format($this->global->date_format. ' g:i:s A');
                }
                else if($active == 'end_date_time'){
                    $data[$active] = $row->end_date_time->format($this->global->date_format. ' g:i:s A');
                }
                else{
                    $data[$active] = $row->{$active};
                }
            }
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        $this->__set('exportData', $exportArray);
        $this->__set('title', $title);
        $this->__set('startDate', Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->format("d M Y"));
        $this->__set('endDate', Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->format("d M Y"));

//        return view('admin.reports.install-schedules.report-pdf', $this->data);
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('admin.reports.install-schedules.report-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }

    public function sendEmail($event_id = null) {

        $event = Event::leftJoin('event_types', 'event_types.id', 'events.event_type')
            ->leftJoin('event_status', 'event_status.id', 'events.status_id')
            ->leftJoin('leads', 'leads.id', 'events.lead_id')
            ->leftJoin('projects', 'projects.id', 'events.project_id')
            ->leftJoin('event_attendees', 'event_attendees.event_id', 'events.id')
            ->select('events.id', 'events.event_name','event_types.type as event_type', 'event_status.name as status', 'events.status_id', 'events.start_date_time', 'events.end_date_time', 'events.created_at', 'event_attendees.user_id as user_id', 'events.project_id', 'events.lead_id', 'events.client_id', 'events.description');

        $event->whereIn('events.event_type', [1,2]);
        $event->where('events.id', $event_id);
        $event = $event->first();

        $activeData = [];
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
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
            }
        }

        $data = [];
        $data['ID'] = $event->id;
        foreach ($activeData as $active) {
            $key = Lang::has('modules.appointments.'.$active) ? __('modules.appointments.'.$active) : __('modules.appointments.'.Str::camel($active));
            if($active == 'designer'){
                $user = User::find($event->user_id);
                $data[$key] = $user->name ?? '';
            }
            else if($active == 'client'){
                if($event->lead_id){
                    $lead = Lead::find($event->lead_id);
                    $data[$key] =  $lead->client->full_name ?? '';
                }
                elseif ($event->project_id){
                    $project = Project::find($event->project_id);
                    $data[$key] =  $project->client->full_name ?? '';
                }
                elseif($event->client_id){
                    $client = Client::find($event->client_id);
                    $data[$key] =  $client->full_name ?? '';
                }
                else{
                    $data[$key] = '';
                }
            }
            else if($active == 'start_date_time'){
                $data[$key] = $event->start_date_time->format($this->global->date_format. ' g:i:s A');
            }
            else if($active == 'end_date_time'){
                $data[$key] = $event->end_date_time->format($this->global->date_format. ' g:i:s A');
            }
            else{
                $data[$key] = $event->{$active};
            }
        }
        $data['Created On'] = $event->created_at->format($this->global->date_format);

        if(!empty($event->user_id)){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($event->user_id);
            $notifyUser->notify(new NewReportEmail($data, $notifyUser, 'appointment'));
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else
            return Reply::error(__('messages.emailSendError'));
    }


}
