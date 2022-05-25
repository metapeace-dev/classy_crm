<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Event;
use App\EventStatus;
use App\Helper\Reply;
use App\Lead;
use App\LeadSource;
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

class LeadSourceReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leadSourceReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->event_status = EventStatus::all();
        return view('admin.reports.lead-source.index', $this->data);
    }

    public function store(Request $request){
    }

    public function data($startDate = null, $endDate = null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = LeadSource::LeftJoin(
            DB::raw('(SELECT lead_sources.id as lead_source_id, COUNT(projects.id) AS project_count, 
                    COUNT(CASE projects.status WHEN "completed" THEN 1 ELSE NULL END) AS sold, 
                    SUM(projects.sales_price) AS amount, AVG(projects.sales_price) AS average_amount 
                    FROM lead_sources LEFT JOIN leads ON leads.`source_id`=lead_sources.id 
                    LEFT JOIN projects ON projects.`id` = leads.`project_id` 
                    WHERE projects.`created_at`>="'.$startDate.'" AND projects.`created_at` <= "'.$endDate.'" GROUP BY lead_sources.id) as m'),
            function ($join) {
            $join->on ( 'm.lead_source_id', '=', 'lead_sources.id' );
        })
        ->select('lead_sources.id', 'lead_sources.name', 'lead_sources.description', 'm.*')->get();

        return DataTables::of($projects)
            ->editColumn('project_count', function ($row) {
                if(!empty($row->project_count))
                    return $row->project_count;
                else
                    return 0;
            })
            ->editColumn('sold', function ($row) {
                if(!empty($row->sold))
                    return $row->sold;
                else
                    return 0;
            })
            ->editColumn('amount', function ($row) {
                if(!empty($row->amount))
                    return '$'.number_format($row->amount, 2, '.', ',');
                else
                    return '$0.00';
            })
            ->editColumn('average_amount', function ($row) {
                if(!empty($row->average_amount))
                    return '$'.number_format($row->average_amount, 2, '.', ',');
                else
                    return '$0.00';
            })
            ->rawColumns([])
            ->make(true);

    }

    public function export($startDate = null, $endDate = null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = LeadSource::LeftJoin(
            DB::raw('(SELECT lead_sources.id as lead_source_id, COUNT(projects.id) AS project_count, 
                    COUNT(CASE projects.status WHEN "completed" THEN 1 ELSE NULL END) AS sold, 
                    SUM(projects.sales_price) AS amount, AVG(projects.sales_price) AS average_amount 
                    FROM lead_sources LEFT JOIN leads ON leads.`source_id`=lead_sources.id 
                    LEFT JOIN projects ON projects.`id` = leads.`project_id` 
                    WHERE projects.`created_at`>="'.$startDate.'" AND projects.`created_at` <= "'.$endDate.'" GROUP BY lead_sources.id) as m'),
            function ($join) {
                $join->on ( 'm.lead_source_id', '=', 'lead_sources.id' );
            })
            ->select('lead_sources.id', 'lead_sources.name', 'lead_sources.description', 'm.*')->get();

        $count_total  = 0; $sold_total = 0; $amount_total = 0; $avg_total = 0;

        $title = 'Lead Summary Report';

        $exportArray[0] = ['ID', 'Name', 'Description', 'Project Count', 'Sold', 'Amount', 'Average Amount'];

        foreach ($projects as $row) {
            $data = [];
            $data['id'] = $row->id;
            $data['name'] = $row->name ?? '';
            $data['description'] = $row->description ?? '';
            $data['project_count'] = $row->project_count ?? 0;
            $data['sold'] = $row->sold ?? 0;
            $data['amount'] = !empty($row->amount) ? '$'.number_format($row->amount, 2, '.', ',') : '$0.00';
            $data['avg_amount'] = !empty($row->average_amount) ? '$'.number_format($row->average_amount, 2, '.', ',') : '$0.00';
            $count_total += $data['project_count'];
            $sold_total += $data['sold'];
            $amount_total += !empty($row->amount) ? $row->amount : 0;
            $avg_total += !empty($row->average_amount) ? $row->average_amount : 0;
            $exportArray[] = $data;
        }
        $total = [];
        $total['id'] = 'Grand Total';
        $total['name'] = '';
        $total['description'] = '';
        $total['project_count'] = $count_total;
        $total['sold'] = $sold_total;
        $total['amount'] = '$'.number_format($amount_total, 2, '.', ',');
        $total['avg_amount'] = '$'.number_format($avg_total/count($projects), 2, '.', ',');
        $exportArray[] = $total;
        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Lead Summary Report file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', true, false);

                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));

                });

            });

        })->download('xlsx');
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

    public function exportPDF(Request $request) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();

        $this->projects = LeadSource::LeftJoin(
            DB::raw('(SELECT lead_sources.id as lead_source_id, COUNT(projects.id) AS project_count, 
                    COUNT(CASE projects.status WHEN "completed" THEN 1 ELSE NULL END) AS sold, 
                    SUM(projects.sales_price) AS amount, AVG(projects.sales_price) AS average_amount 
                    FROM lead_sources LEFT JOIN leads ON leads.`source_id`=lead_sources.id 
                    LEFT JOIN projects ON projects.`id` = leads.`project_id` 
                    WHERE projects.`created_at`>="'.$startDate.'" AND projects.`created_at` <= "'.$endDate.'" GROUP BY lead_sources.id) as m'),
            function ($join) {
                $join->on ( 'm.lead_source_id', '=', 'lead_sources.id' );
            })
            ->select('lead_sources.id', 'lead_sources.name', 'lead_sources.description', 'm.*')->get();

        $this->__set('startDate', Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->format("d M Y"));
        $this->__set('endDate', Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->format("d M Y"));

        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('admin.reports.lead-source.report-pdf', $this->data);
        return $pdf->download('Lead Summary Report.pdf');
    }
}
